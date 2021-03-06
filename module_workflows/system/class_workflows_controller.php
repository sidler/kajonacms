<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/

/**
 * The controller triggers the execution of scheduled workflows and manages the transition of
 * workflows' states.
 *
 * @package module_workflows
 * @author sidler@mulchprod.de
 */
class class_workflows_controller   {

    const STR_LOGFILE = "workflows.log";


    /**
     * Searches for new workflows and forces them to schedule and initialize
     */
    public function scheduleWorkflows() {
        $arrWorkflows = class_module_workflows_workflow::getWorkflowsByType(class_module_workflows_workflow::$INT_STATE_NEW, false);

        class_logger::getInstance(self::STR_LOGFILE)->addLogRow("scheduling workflows, count: ".count($arrWorkflows), class_logger::$levelInfo);

        foreach($arrWorkflows as $objOneWorkflow) {

            //lock the workflow
            $objLockmanager = new class_lockmanager($objOneWorkflow->getSystemid());
            if($objLockmanager->isLocked()) {
                class_logger::getInstance(self::STR_LOGFILE)->addLogRow("workflow ".$objOneWorkflow->getSystemid()." is locked, can't be scheduled", class_logger::$levelWarning);
                continue;
            }

            $objLockmanager->lockRecord();

            /**
             * @var interface_workflows_handler
             */
            $objHandler = $objOneWorkflow->getObjWorkflowHandler();

            //trigger the workflow
            class_logger::getInstance(self::STR_LOGFILE)->addLogRow("scheduling workflow ".$objOneWorkflow->getSystemid(), class_logger::$levelInfo);
            if($objOneWorkflow->getObjTriggerdate() == null)
                $objOneWorkflow->setObjTriggerdate(new class_date());
            $objHandler->schedule();

            class_logger::getInstance(self::STR_LOGFILE)->addLogRow(" scheduling finished, new state: scheduled", class_logger::$levelInfo);
            $objOneWorkflow->setIntState(class_module_workflows_workflow::$INT_STATE_SCHEDULED);

            //init happened before
            $objOneWorkflow->updateObjectToDb();

            //unlock
            $objLockmanager->unlockRecord(true);
            $objOneWorkflow->getLockManager()->unlockRecord(true);


        }
    }



    /**
     * Triggers the workflows scheduled for running.
     */
    public function runWorkflows() {
        $arrWorkflows = class_module_workflows_workflow::getWorkflowsByType(class_module_workflows_workflow::$INT_STATE_SCHEDULED);

        class_logger::getInstance(self::STR_LOGFILE)->addLogRow("running workflows, count: ".count($arrWorkflows), class_logger::$levelInfo);

        foreach($arrWorkflows as $objOneWorkflow) {

            //lock the workflow
            $objLockmanager = new class_lockmanager($objOneWorkflow->getSystemid());
            if($objLockmanager->isLocked()) {
                class_logger::getInstance(self::STR_LOGFILE)->addLogRow("workflow ".$objOneWorkflow->getSystemid()." is locked, can't be scheduled", class_logger::$levelWarning);
                continue;
            }

            $objLockmanager->lockRecord();

            /**
             * @var interface_workflows_handler
             */
            $objHandler = $objOneWorkflow->getObjWorkflowHandler();

            //trigger the workflow
            class_logger::getInstance(self::STR_LOGFILE)->addLogRow("executing workflow ".$objOneWorkflow->getSystemid(), class_logger::$levelInfo);
            if($objHandler->execute()) {
                //handler executed successfully. shift to state 'executed'
                $objOneWorkflow->setIntState(class_module_workflows_workflow::$INT_STATE_EXECUTED);
                class_logger::getInstance(self::STR_LOGFILE)->addLogRow(" execution finished, new state: executed", class_logger::$levelInfo);
            }
            else {
                //handler failed to execute. reschedule.
                $objHandler->schedule();
                $objOneWorkflow->setIntState(class_module_workflows_workflow::$INT_STATE_SCHEDULED);
                class_logger::getInstance(self::STR_LOGFILE)->addLogRow(" execution finished, new state: scheduled", class_logger::$levelInfo);
            }

            $objOneWorkflow->setIntRuns($objOneWorkflow->getIntRuns()+1);
            $objOneWorkflow->updateObjectToDb();

            $objLockmanager->unlockRecord(true);

        }
    }

    /**
     * Runs a single workflow.
     * @param class_module_workflows_workflow $objOneWorkflow
     */
    public function runSingleWorkflow($objOneWorkflow) {

        $objHandler = $objOneWorkflow->getObjWorkflowHandler();

        if($objOneWorkflow->getIntState() != class_module_workflows_workflow::$INT_STATE_SCHEDULED)
            return;

        //trigger the workflow
        class_logger::getInstance(self::STR_LOGFILE)->addLogRow("executing workflow ".$objOneWorkflow->getSystemid(), class_logger::$levelInfo);
        if($objHandler->execute()) {
            //handler executed successfully. shift to state 'executed'
            $objOneWorkflow->setIntState(class_module_workflows_workflow::$INT_STATE_EXECUTED);
            class_logger::getInstance(self::STR_LOGFILE)->addLogRow(" execution finished, new state: executed", class_logger::$levelInfo);
        }
        else {
            //handler failed to execute. reschedule.
            $objHandler->schedule();
            $objOneWorkflow->setIntState(class_module_workflows_workflow::$INT_STATE_SCHEDULED);
            class_logger::getInstance(self::STR_LOGFILE)->addLogRow(" execution finished, new state: scheduled", class_logger::$levelInfo);
        }

        $objOneWorkflow->setIntRuns($objOneWorkflow->getIntRuns()+1);
        $objOneWorkflow->updateObjectToDb();

    }
    
}

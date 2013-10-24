<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/
//Edited with Kajona Language Editor GUI, see www.kajona.de and www.mulchprod.de for more information
//Kajona Language Editor Core Build 389

//editable entries
$lang["_admin_nr_of_rows_"]              = "Número de registos por página";
$lang["_admin_nr_of_rows_hint"]          = "Número de registos na lista administrativa, se suportados pelo módulo. Pode ser redefenido por um módulo!";
$lang["_admin_only_https_"]              = "Administrador só via HTTPS";
$lang["_admin_only_https_hint"]          = "Forçar o uso de HTTPS quando carregar a administração. O servidor de internet tem de suportar HTTPS para usar esta opção.";
$lang["_remoteloader_max_cachetime_"]    = "Memória temporária de fontes externas";
$lang["_remoteloader_max_cachetime_hint"] = "Tempo em segundos para carregar conteúdos do exterior (ex: RSS-Feeds).";
$lang["_system_admin_email_"]            = "Correio Electrónico Administrador";
$lang["_system_admin_email_hint"]        = "Se um endereço é introduzido, uma mensagem de correio electrónico é enviada no caso de surgirem erros críticos.";
$lang["_system_browser_cachebuster_"]    = "Browser-Cachebuster";
$lang["_system_browser_cachebuster_hint"] = "Esse valor é acrescentado como GET parâmetro para todas as referências a JS / CSS. Ao incrementar esse valor o navegador será obrigado a recarregar os arquivos do servidor, independentemente das configurações de cache dos navegadores e os cabeçalhos HTTP enviados. O valor pode ser aumentado automaticamente por uma tarefa do sistema.";
$lang["_system_dbdump_amount_"]          = "Número de DB-dumps";
$lang["_system_dbdump_amount_hint"]      = "Define o número de DB-dumps que devem ser mantidos.";
$lang["_system_lock_maxtime_"]           = "Máximo Tempo de Bloqueio";
$lang["_system_lock_maxtime_hint"]       = "Depois da duração dada em segundos, os registos bloqueados serão desbloqueados automaticamente.";
$lang["_system_mod_rewrite_"]            = "Endereço(URL) reescrito";
$lang["_system_mod_rewrite_hint"]        = "Activa/Desactiva endereço(URL) reescrito para endereços personalizados. O módulo apache \"mod_rewrite\" tem de ser instalado e activado no ficheiro .htaccess, para permitir o uso desta opção. ";
$lang["_system_portal_disable_"]         = "Desactivar sistema(portal)";
$lang["_system_portal_disable_hint"]     = "Activar/Desactivar todo o sistema(portal).";
$lang["_system_portal_disablepage_"]     = "Página temporária";
$lang["_system_portal_disablepage_hint"] = "Esta página é visualizada, se o sistema(portal) se encontrar desactivado.";
$lang["_system_release_time_"]           = "Duração da sessão";
$lang["_system_release_time_hint"]       = "Depois deste tempo em segundos a sesão torna-se inválida.";
$lang["_system_use_dbcache_"]            = "Memória Temporária da Base de Dados";
$lang["_system_use_dbcache_hint"]        = "Activa/Desactiva a memória temporária de pesquisa interna na base de dados(database query).";
$lang["about_part1"]                     = "<h2>Kajona V4 - Sistema de Gestão de Conteúdos em Código Aberto</h2>Kajona V 4.3, Nome de código \"streamline\"<br /><br /><a href=\"http://www.kajona.de\" target=\"_blank\">www.kajona.de</a><br /><a href=\"mailto:info@kajona.de\" target=\"_blank\">info@kajona.de</a><br /><br />Para mais informações, suporte ou sugestões, Por favor visite o nosso endereço.<br />Suporte adicional é dado no nosso <a href=\"http://board.kajona.de/\" target=\"_blank\">fórum</a>.";
$lang["about_part2_header"]              = "<h2>Responsável do projecto</h2>";
$lang["about_part2a_header"]             = "<h2>Contribuições / Programadores</h2>";
$lang["about_part2b_header"]             = "<h2>Traduções</h2>";
$lang["about_part4"]                     = "<h2>Doação</h2><p>Se você gosta de trabalhar com Kajona e pretende apoiar o projeto, pode doar para o projeto: </p> <form method=\"post\" action=\"https://www.paypal.com/cgi-bin/webscr\" target=\"_blank\"><input type=\"hidden\" value=\"_donations\" name=\"cmd\" /> <input type=\"hidden\" value=\"donate@kajona.de\" name=\"business\" /> <input type=\"hidden\" value=\"Kajona Development\" name=\"item_name\" /> <input type=\"hidden\" value=\"0\" name=\"no_shipping\" /> <input type=\"hidden\" value=\"1\" name=\"no_note\" /> <input type=\"hidden\" value=\"EUR\" name=\"currency_code\" /> <input type=\"hidden\" value=\"0\" name=\"tax\" /> <input type=\"hidden\" value=\"PP-DonationsBF\" name=\"bn\" /> <input type=\"submit\" name=\"submit\" value=\"Doar via PayPal\" class=\"inputSubmit\" /></form>";
$lang["action_about"]                    = "Sobre Kajona";
$lang["action_list"]                     = "Módulos instalados";
$lang["action_system_info"]              = "Informação Sistema";
$lang["action_system_sessions"]          = "Sessões";
$lang["action_system_settings"]          = "Propriedades do sistema";
$lang["action_system_tasks"]             = "Tarefas Sistema";
$lang["action_systemlog"]                = "Eventos do sistema";
$lang["anzahltabellen"]                  = "Número de Tabelas";
$lang["dateStyleLong"]                   = "d/m/Y H:i:s";
$lang["dateStyleShort"]                  = "d/m/Y";
$lang["datenbankclient"]                 = "Base de Dados Cliente";
$lang["datenbankserver"]                 = "Servidor Base de Dados";
$lang["datenbanktreiber"]                = "Controlador Base de Dados";
$lang["datenbankverbindung"]             = "Ligação Base de Dados";
$lang["db"]                              = "Base de Dados";
$lang["desc"]                            = "Editar permissões de";
$lang["dialog_cancelButton"]             = "cancelar";
$lang["dialog_deleteButton"]             = "Sim, eliminar";
$lang["dialog_deleteHeader"]             = "Confirmar eliminação";
$lang["dialog_loadingHeader"]            = "Aguarde";
$lang["diskspace_free"]                  = " (livre/total)";
$lang["errorintro"]                      = "Por favor forneça todos os valares necessários!";
$lang["errorlevel"]                      = "Erro de nível";
$lang["executiontimeout"]                = "Tempo de execução terminado";
$lang["fehler_setzen"]                   = "Erro a guardar permissões";
$lang["filebrowser"]                     = "Escolha o arquivo";
$lang["gd"]                              = "GD-Lib";
$lang["geladeneerweiterungen"]           = "Extensões carregadas";
$lang["gifread"]                         = "Suporte de Leitura GIF";
$lang["gifwrite"]                        = "Suporte de Escrita GIF";
$lang["groessedaten"]                    = "Tamanho dos dados";
$lang["groessegesamt"]                   = "Tamanho total";
$lang["inputtimeout"]                    = "Tempo de entrada terminado";
$lang["jpg"]                             = "Suporte JPG";
$lang["keinegd"]                         = "GD-Lib não instalada";
$lang["log_empty"]                       = "Não existem registo de entradas do ficheiro de acessos.";
$lang["memorylimit"]                     = "Limite de memória";
$lang["modul_rechte_root"]               = "Direitos do registo na raiz";
$lang["modul_sortdown"]                  = "Para baixo";
$lang["modul_sortup"]                    = "Para cima";
$lang["modul_status_disabled"]           = "Activar o módulo (está inactivo)";
$lang["modul_status_enabled"]            = "Desactivar o módulo (está activo)";
$lang["modul_status_system"]             = "Ooops, queres desactivar o kernel do sistema? Bem, para fazeres isto é melhor formates o teu computador! Mas na raiz c: para ele não fugir... eheheh ;-)";
$lang["modul_titel"]                     = "Sistema";
$lang["moduleRightsTitle"]               = "Permissões";
$lang["operatingsystem"]                 = "Sistema";
$lang["pageview_forward"]                = "Seguinte";
$lang["pageview_total"]                  = "Total";
$lang["php"]                             = "PHP";
$lang["png"]                             = "Suporte PNG";
$lang["postmaxsize"]                     = "Tamanho máximo da entrada";
$lang["quickhelp_change"]                = "Utilizando este formulário, poderá ajustar as permissões do registo.<br />Dependendo do módulo a que o registo pertence, os diferentes tipos de permissões podem variar. ";
$lang["quickhelp_list"]                  = "A lista de módulos fornece uma visão dos módulos actualmente instalados.<br />Adicionalmente, as versões dos módulos e as datas de instalação são mostradas.<br />Terá a possibilidade de alterar as permissões do registo de direitos do módulo, a base para todos os conteúdos para interagir com as suas permissões (se activado).<br />É, também possível, reordenar os módulos no sistema de Navegação de Módulos, alterando a posição do módulo na lista.";
$lang["quickhelp_module_list"]           = "A lista de módulos fornece uma visão dos módulos actualmente instalados.<br />Adicionalmente, as versões dos módulos e as datas de instalação são mostradas.<br />Terá a possibilidade de alterar as permissões do registo de direitos do módulo, a base para todos os conteúdos para interagir com as suas permissões (se activado).<br />É, também possível, reordenar os módulos no sistema de Navegação de Módulos, alterando a posição do módulo na lista.";
$lang["quickhelp_system_info"]           = "O Kajona tenta descobrir algumas informações sobre o ambiente onde o Kajona está a correr.";
$lang["quickhelp_system_settings"]       = "Pode configurar predefinições básicas para o sistema. E aí, cada módulo pode permitir outras inumeras configurações. As alterações feitas deveram ser cuidadas, pois valores incorrectos podem tornar o sistema instável ou mesmo inútil.<br /><br />Nota: Se existem alterações realizadas a um determinado módulo, terá de guardar todos os novos valores para todos os módulos! Alterações em outros módulos serão ignoradas! Quando primir o botão Guardar, só os valores correspondentes serão guardados.";
$lang["quickhelp_system_tasks"]          = "As tarefas de sistema são pequenos programas que correm durante todos os dias de trabalho.<br />Isto inclui tarefas para realizar cópias de segurança à base de dados ou para restaurar as cópias de segurança antes criadas.";
$lang["quickhelp_systemlog"]             = "O sistema de eventos mostra todas as entradas/acessos no ficheiro de registo de acessos.<br />A granularidade do motor de registo de eventos pode ser configurado no ficheiro config (/system/config/config.php).";
$lang["quickhelp_title"]                 = "Ajuda rápida";
$lang["quickhelp_updateCheck"]           = "Através do uso do sistema de verificação de actualizações, a versão dos módulos instalados localmente e as versões dos módulos disponíveis online serão comparados. Se existir uma nova versão disponível, o Kajona disponibilizará uma mensagem de notificação no respectivo módulo.";
$lang["server"]                          = "Servidor de Internet";
$lang["session_activity"]                = "Actividade";
$lang["session_admin"]                   = "Administração, módulo";
$lang["session_loggedin"]                = "Ligado em";
$lang["session_loggedout"]               = "Convidado";
$lang["session_logout"]                  = "Sessão inválida";
$lang["session_portal"]                  = "Portal, página";
$lang["session_status"]                  = "Estado";
$lang["session_username"]                = "nome de utilizador";
$lang["session_valid"]                   = "Válido até";
$lang["setAbsolutePosOk"]                = "Posição guarda com sucesso";
$lang["setStatusError"]                  = "Erro na alteração do estado";
$lang["setStatusOk"]                     = "A alteração do estado ocorreu com sucesso.";
$lang["settings_updated"]                = "Propriedades alteradas com sucesso";
$lang["setzen_erfolg"]                   = "Permissões guardadas com sucesso";
$lang["speichern"]                       = "Guardar";
$lang["speicherplatz"]                   = "Espaço em disco";
$lang["status_active"]                   = "Alterar estado (está activo)";
$lang["status_inactive"]                 = "Alterar estado (está inactivo)";
$lang["systeminfo_php_regglobal"]        = "Register globals";
$lang["systeminfo_php_safemode"]         = "Safe mode";
$lang["systeminfo_php_urlfopen"]         = "Allow url fopen";
$lang["systeminfo_webserver_modules"]    = "Módulos carregados";
$lang["systeminfo_webserver_version"]    = "Servidor de Internet";
$lang["systemtask_dbconsistency_curprev_error"] = "As seguintes relações de nós (pai-filho) estão incorrectas (ligação pai em falta)";
$lang["systemtask_dbconsistency_curprev_ok"] = "Todas as relações dos nós (pai-filho) estão correctas";
$lang["systemtask_dbconsistency_date_error"] = "As seguintes datas de registo estão incorrectas (registos de sistema em falta)";
$lang["systemtask_dbconsistency_date_ok"] = "Todos os registos de datas tem correspondência com os registos do sistema";
$lang["systemtask_dbconsistency_name"]   = "Verificar consistência da Base de Dados";
$lang["systemtask_dbconsistency_right_error"] = "Os seguintes direitos de registo estão incorrectos (registos de sistema em falta)";
$lang["systemtask_dbconsistency_right_ok"] = "Todos os direitos de registo correspondem com os direitos do sistema";
$lang["systemtask_dbexport_error"]       = "Erro a exportar a Base de Dados";
$lang["systemtask_dbexport_name"]        = "Cópia de Segurança da Base de Dados";
$lang["systemtask_dbexport_success"]     = "Cópia de segurança criada com sucesso";
$lang["systemtask_dbimport_error"]       = "Erro no restauro da cópia de segurança";
$lang["systemtask_dbimport_file"]        = "Cópia de Segurança";
$lang["systemtask_dbimport_name"]        = "Importar cópia de segurança da Base de Dados";
$lang["systemtask_dbimport_success"]     = "Restauro da cópia de segurança realizado com sucesso";
$lang["systemtask_flushpiccache_deleted"] = "<br />Número de ficheiros eliminados: ";
$lang["systemtask_flushpiccache_done"]   = "Descarregamento completo.";
$lang["systemtask_flushpiccache_name"]   = "Descarregar memória temporária das imagens";
$lang["systemtask_flushpiccache_skipped"] = "<br />Número de ficheiros omitidos: ";
$lang["systemtask_run"]                  = "Realizar";
$lang["titel_erben"]                     = "Direitos hierarquicos";
$lang["titel_leer"]                      = "<em>sem título defenido</em>";
$lang["titel_root"]                      = "Direitos gravação na raiz";
$lang["toolsetCalendarMonth"]            = "\"Janeiro\", \"Fevereiro\", \"Março\", \"Abril\", \"Maio\", \"Junho\", \"Julho\", \"Agosto\", \"Setembro\", \"Outubro\", \"Novembro\", \"Dezembro\"";
$lang["toolsetCalendarWeekday"]          = "\"Dom\", \"Seg\", \"Ter\", \"Qua\", \"Qui\", \"Sex\", \"Sáb\"";
$lang["update_available"]                = "Por favor, actualize!";
$lang["update_invalidXML"]               = "A resposta dos servidores foi devolvida com erro. Por favor, tente novamente.";
$lang["update_module_localversion"]      = "Esta instalação";
$lang["update_module_name"]              = "Módulo";
$lang["update_module_remoteversion"]     = "Disponível";
$lang["update_nodom"]                    = "A instalação PHP não suporta XML-DOM. Esta funcionalidade é necessária para a verificar a actualização em funcionamento.";
$lang["update_nofilefound"]              = "A lista de actualizações falhou no carregamento.<br />As razões possíveis podem estar relacionadas com o valor da função 'allow_url_fopen' no ficheiro php-config estar a 'off' ou então estar a usar um servidor, onde o sistema não tem suporte para ligações bidireccionais (mais conhecidas como 'sockets').";
$lang["update_nourlfopen"]               = "Para fazer esta função funcionar, o valor &apos;allow_url_fopen&apos; precisa de ser estabelecido &apos;on&apos; no ficheiro php-config!";
$lang["uploadmaxsize"]                   = "Tamanho máximo do ficheiro";
$lang["uploads"]                         = "Ficheiros carregados";
$lang["version"]                         = "Versão";
$lang["warnung_settings"]                = "!! ATENÇÃO !!<br />O uso de vaores incorrectos para as seguintes preferências pode tornar os sistema instável ou mesmo inutilizado!";

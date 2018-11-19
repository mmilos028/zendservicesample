<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    //initialize default settings used for application
    protected function _initSettings(){
        Zend_Locale::setDefault('en_US');
    }

    //initialize database and database profiler
    protected function _initDatabase(){
        $config = Zend_Registry::get('config');
        $params = array(
            'dbname' => $config->db->dbname,
            'username' => $config->db->username,
            'password' => $config->db->password,
            'persistent' => $config->db->persistent,
            'charset' => $config->db->charset
        );
        $db = Zend_Db::factory($config->db->adapter, $params);
        Zend_Registry::set('db_auth', $db);
        unset($db);

        if($config->db->enable_cache == "true"){
            //database cache-ing
            require_once 'Zend/Cache/Manager.php';
            $disable_caching = false;
            $frontend = array(
                'lifetime' => 600,
                'automatic_serialization' => true,
                'disable_caching' => $disable_caching,
            );
            $backend = array(
                'lifetime' => 600,
                'cache_dir' => $config->db->cache_location,
                'automatic_serialization' => true,
                'disable_caching' => $disable_caching,
            );

            $cache = Zend_Cache::factory('Core', 'File', $frontend, $backend);
            Zend_Registry::set('db_cache', $cache);
        }
    }

    protected function _initDatabaseAts(){
        $config = Zend_Registry::get('config');
        $params = array(
           'dbname' => $config->db->dbname,
           'username' => $config->db_ats->username,
           'password' => $config->db_ats->password,
           'persistent' => $config->db_ats->persistent
        );
        $db_ats = Zend_Db::factory($config->db->adapter, $params);
        Zend_Registry::set('db_ats_auth', $db_ats);
        unset($db_ats);
    }

    //initialize routes for application URL's
    protected function _initRoutes()
    {
        $this->bootstrap('FrontController');
        $frontController = $this->getResource('frontController');
        $frontController->setControllerDirectory( array(
                'default' => APP_DIR . DS . 'controllers',
                'html' => APP_DIR . DS . 'controllers/Html',
                'rest' => APP_DIR . DS . 'controllers/Rest',
                'terminal-integration-rest' => APP_DIR . DS . 'controllers/TerminalIntegrationRest',
            )
        );
    }

    //initialize and set locale for application
    protected function _initLocale(){
        $locale = new Zend_Locale('en_US');
        Zend_Registry::set('Zend_Locale', $locale);
    }

    //initialize onlinecasinoservice loggers
    protected function _initOnlinecasinoserviceLoggers(){
        $config = Zend_Registry::get('config');
        //onlinecasinoservice error log
        $writeErrorLogFile = $config->writeErrorLogFile;
        $errorPathFile = $config->errorPathFile;
        $errorLogSize = $config->errorLogSize;
        //onlinecasinoservice access log
        $writeAccessLogFile = $config->writeAccessLogFile;
        $accessPathFile = $config->accessPathFile;
        $accessLogSize = $config->accessLogSize;
        try{
            //ONLINECASINO LOG SETUP
            //error log for onlinecasinoservice
            if(file_exists($errorPathFile)){
                if(filesize($errorPathFile) >= $errorLogSize * 1024 * 1024){ //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($errorPathFile, ".txt");
                    $file_path = dirname($errorPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($errorPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($errorPathFile);
            $error_logger = new Zend_Log();
            $error_logger->addWriter($writerFile);
            Zend_Registry::set('error_logger', $error_logger);
            //access log for onlinecasinoservice
            if(file_exists($accessPathFile)){
                if(filesize($accessPathFile) >= $accessLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($accessPathFile, ".txt");
                    $file_path = dirname($accessPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($accessPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($accessPathFile);
            $access_logger = new Zend_Log();
            $access_logger->addWriter($writerFile);
            Zend_Registry::set('access_logger', $access_logger);
        }catch(Exception $ex){
        }
    }

    //initialize website loggers
    protected function _initWebsiteLoggers(){
        $config = Zend_Registry::get('config');
        //site error log
        $writeSiteErrorLogFile = $config->writeSiteErrorLogFile;
        $siteErrorPathFile = $config->siteErrorPathFile;
        $siteErrorLogSize = $config->siteErrorLogSize;
        //site access log
        $writeSiteAccessLogFile = $config->writeSiteAccessLogFile;
        $siteAccessPathFile = $config->siteAccessPathFile;
        $siteAccessLogSize = $config->siteAccessLogSize;
        try{
            //SITE SERVICE LOG SETUP
            //error log for site service
            if(file_exists($siteErrorPathFile)){
                if(filesize($siteErrorPathFile) >= $siteErrorLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($siteErrorPathFile, ".txt");
                    $file_path = dirname($siteErrorPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($siteErrorPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($siteErrorPathFile);
            $site_error_logger = new Zend_Log();
            $site_error_logger->addWriter($writerFile);
            Zend_Registry::set('site_error_logger', $site_error_logger);
            //access log for site service
            if(file_exists($siteAccessPathFile)){
                if(filesize($siteAccessPathFile) >= $siteAccessLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($siteAccessPathFile, ".txt");
                    $file_path = dirname($siteAccessPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($siteAccessPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($siteAccessPathFile);
            $site_access_logger = new Zend_Log();
            $site_access_logger->addWriter($writerFile);
            Zend_Registry::set('site_access_logger', $site_access_logger);
        }catch(Exception $ex){
        }
    }

    //initialize merchant loggers
    protected function _initMerchantLoggers(){
        $config = Zend_Registry::get('config');
        //merchant error log
        $writeMerchantErrorLogFile = $config->writeMerchantErrorLogFile;
        $merchantErrorPathFile = $config->merchantErrorPathFile;
        $merchantErrorLogSize = $config->merchantErrorLogSize;
        //merchant access log
        $writeMerchantAccessLogFile = $config->writeMerchantAccessLogFile;
        $merchantAccessPathFile = $config->merchantAccessPathFile;
        $merchantAccessLogSize = $config->merchantAccessLogSize;
        //merchant declined transactions log
        $writeMerchantDeclinedTransactionsLogFile = $config->writeMerchantDeclinedTransactionsLogFile;
        $merchantDeclinedTransactionsPathFile = $config->merchantDeclinedTransactionsPathFile;
        $merchantDeclinedTransactionsLogSize = $config->merchantDeclinedTransactionsLogSize;
        try{
            //MERCHANT LOG SETUP
            //error log for merchant service
            if(file_exists($merchantErrorPathFile)){
                if(filesize($merchantErrorPathFile) >= $merchantErrorLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($merchantErrorPathFile, ".txt");
                    $file_path = dirname($merchantErrorPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($merchantErrorPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($merchantErrorPathFile);
            $merchant_error_logger = new Zend_Log();
            $merchant_error_logger->addWriter($writerFile);
            Zend_Registry::set('merchant_error_logger', $merchant_error_logger);
            //access log for merchant service
            if(file_exists($merchantAccessPathFile)){
                if(filesize($merchantAccessPathFile) >= $merchantAccessLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($merchantAccessPathFile, ".txt");
                    $file_path = dirname($merchantAccessPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($merchantAccessPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($merchantAccessPathFile);
            $merchant_access_logger = new Zend_Log();
            $merchant_access_logger->addWriter($writerFile);
            Zend_Registry::set('merchant_access_logger', $merchant_access_logger);
            //declined transactions log for merchant service
            if(file_exists($merchantDeclinedTransactionsPathFile)){
                if(filesize($merchantDeclinedTransactionsPathFile) >= $merchantDeclinedTransactionsLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($merchantDeclinedTransactionsPathFile, ".txt");
                    $file_path = dirname($merchantDeclinedTransactionsPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($merchantDeclinedTransactionsPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($merchantDeclinedTransactionsPathFile);
            $merchant_declined_transactions_logger = new Zend_Log();
            $merchant_declined_transactions_logger->addWriter($writerFile);
            Zend_Registry::set('merchant_declined_transactions_logger', $merchant_declined_transactions_logger);

        }catch(Exception $ex){
        }
    }

    //initialize GGL Integration loggers
    protected function _initGGLIntegrationLoggers(){
        $config = Zend_Registry::get('config');
        //ldc integration error log
        $writeLdcIntegrationErrorLogFile = $config->writeLdcIntegrationErrorLogFile;
        $ldcIntegrationErrorPathFile = $config->ldcIntegrationErrorPathFile;
        $ldcIntegrationErrorLogSize = $config->ldcIntegrationErrorLogSize;
        //ldc integration access log
        $writeLdcIntegrationAccessLogFile = $config->writeLdcIntegrationAccessLogFile;
        $ldcIntegrationAccessPathFile = $config->ldcIntegrationAccessPathFile;
        $ldcIntegrationAccessLogSize = $config->ldcIntegrationAccessLogSize;
        try{
            //LDC Integration LOG SETUP
            //error log for ldc integration service
            if(file_exists($ldcIntegrationErrorPathFile)){
                if(filesize($ldcIntegrationErrorPathFile) >= $ldcIntegrationErrorLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($ldcIntegrationErrorPathFile, ".txt");
                    $file_path = dirname($ldcIntegrationErrorPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($ldcIntegrationErrorPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($ldcIntegrationErrorPathFile);
            $ldc_integration_error_logger = new Zend_Log();
            $ldc_integration_error_logger->addWriter($writerFile);
            Zend_Registry::set('ldc_integration_error_logger', $ldc_integration_error_logger);
            //access log for ldc integration service
            if(file_exists($ldcIntegrationAccessPathFile)){
                if(filesize($ldcIntegrationAccessPathFile) >= $ldcIntegrationAccessLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($ldcIntegrationAccessPathFile, ".txt");
                    $file_path = dirname($ldcIntegrationAccessPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($ldcIntegrationAccessPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($ldcIntegrationAccessPathFile);
            $ldc_integration_access_logger = new Zend_Log();
            $ldc_integration_access_logger->addWriter($writerFile);
            Zend_Registry::set('ldc_integration_access_logger', $ldc_integration_access_logger);
        }catch(Exception $ex){
        }
    }

    //initialize Vivo Gaming Integration loggers
    protected function _initVivoGamingIntegrationLoggers(){
        $config = Zend_Registry::get('config');
        //vivo gaming integration error log
        $writeVivoGamingIntegrationErrorLogFile = $config->writeVivoGamingIntegrationErrorLogFile;
        $vivoGamingIntegrationErrorPathFile = $config->vivoGamingIntegrationErrorPathFile;
        $vivoGamingIntegrationErrorLogSize = $config->vivoGamingIntegrationErrorLogSize;
        //VivoGaming integration access log
        $writeVivoGamingIntegrationAccessLogFile = $config->writeVivoGamingIntegrationAccessLogFile;
        $vivoGamingIntegrationAccessPathFile = $config->vivoGamingIntegrationAccessPathFile;
        $vivoGamingIntegrationAccessLogSize = $config->vivoGamingIntegrationAccessLogSize;
        try{
            //VivoGaming Integration LOG SETUP
            //error log for VivoGaming integration service
            if(file_exists($vivoGamingIntegrationErrorPathFile)){
                if(filesize($vivoGamingIntegrationErrorPathFile) >= $vivoGamingIntegrationErrorLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($vivoGamingIntegrationErrorPathFile, ".txt");
                    $file_path = dirname($vivoGamingIntegrationErrorPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($vivoGamingIntegrationErrorPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($vivoGamingIntegrationErrorPathFile);
            $vivo_gaming_integration_error_logger = new Zend_Log();
            $vivo_gaming_integration_error_logger->addWriter($writerFile);
            Zend_Registry::set('vivo_gaming_integration_error_logger', $vivo_gaming_integration_error_logger);
            //access log for vivo gaming integration service
            if(file_exists($vivoGamingIntegrationAccessPathFile)){
                if(filesize($vivoGamingIntegrationAccessPathFile) >= $vivoGamingIntegrationAccessLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($vivoGamingIntegrationAccessPathFile, ".txt");
                    $file_path = dirname($vivoGamingIntegrationAccessPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($vivoGamingIntegrationAccessPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($vivoGamingIntegrationAccessPathFile);
            $vivo_gaming_integration_access_logger = new Zend_Log();
            $vivo_gaming_integration_access_logger->addWriter($writerFile);
            Zend_Registry::set('vivo_gaming_integration_access_logger', $vivo_gaming_integration_access_logger);
        }catch(Exception $ex){
        }
    }

    //initialize CBCX Integration loggers
    protected function _initCbcxIntegrationLoggers(){
        $config = Zend_Registry::get('config');
        //cbcx integration error log
        $writeCbcxIntegrationErrorLogFile = $config->writeCbcxIntegrationErrorLogFile;
        $cbcxIntegrationErrorPathFile = $config->cbcxIntegrationErrorPathFile;
        $cbcxIntegrationErrorLogSize = $config->cbcxIntegrationErrorLogSize;
        //cbcx integration access log
        $writeCbcxIntegrationAccessLogFile = $config->writeCbcxIntegrationAccessLogFile;
        $cbcxIntegrationAccessPathFile = $config->cbcxIntegrationAccessPathFile;
        $cbcxIntegrationAccessLogSize = $config->cbcxIntegrationAccessLogSize;
        try{
            //CBCX Integration LOG SETUP
            //error log for cbcx integration service
            if(file_exists($cbcxIntegrationErrorPathFile)){
                if(filesize($cbcxIntegrationErrorPathFile) >= $cbcxIntegrationErrorLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($cbcxIntegrationErrorPathFile, ".txt");
                    $file_path = dirname($cbcxIntegrationErrorPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($cbcxIntegrationErrorPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($cbcxIntegrationErrorPathFile);
            $cbcx_integration_error_logger = new Zend_Log();
            $cbcx_integration_error_logger->addWriter($writerFile);
            Zend_Registry::set('cbcx_integration_error_logger', $cbcx_integration_error_logger);
            //access log for ldc integration service
            if(file_exists($cbcxIntegrationAccessPathFile)){
                if(filesize($cbcxIntegrationAccessPathFile) >= $cbcxIntegrationAccessLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($cbcxIntegrationAccessPathFile, ".txt");
                    $file_path = dirname($cbcxIntegrationAccessPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($cbcxIntegrationAccessPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($cbcxIntegrationAccessPathFile);
            $cbcx_integration_access_logger = new Zend_Log();
            $cbcx_integration_access_logger->addWriter($writerFile);
            Zend_Registry::set('cbcx_integration_access_logger', $cbcx_integration_access_logger);
        }catch(Exception $ex){
        }
    }

    //initialize Sport Betting Integration loggers
    protected function _initSportBettingIntegrationLoggers(){
        $config = Zend_Registry::get('config');
        //SPORT BETTING integration error log
        $writeSportBettingIntegrationErrorLogFile = $config->writeSportBettingIntegrationErrorLogFile;
        $sportBettingIntegrationErrorPathFile = $config->sportBettingIntegrationErrorPathFile;
        $sportBettingIntegrationErrorLogSize = $config->sportBettingIntegrationErrorLogSize;
        //SPORT BETTING integration access log
        $writeSportBettingIntegrationAccessLogFile = $config->writeSportBettingIntegrationAccessLogFile;
        $sportBettingIntegrationAccessPathFile = $config->sportBettingIntegrationAccessPathFile;
        $sportBettingIntegrationAccessLogSize = $config->sportBettingIntegrationAccessLogSize;
        try{
            //SPORT BETTING Integration LOG SETUP
            //error log for sport betting integration service
            if(file_exists($sportBettingIntegrationErrorPathFile)){
                if(filesize($sportBettingIntegrationErrorPathFile) >= $sportBettingIntegrationErrorLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($sportBettingIntegrationErrorPathFile, ".txt");
                    $file_path = dirname($sportBettingIntegrationErrorPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($sportBettingIntegrationErrorPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($sportBettingIntegrationErrorPathFile);
            $sport_betting_integration_error_logger = new Zend_Log();
            $sport_betting_integration_error_logger->addWriter($writerFile);
            Zend_Registry::set('sport_betting_integration_error_logger', $sport_betting_integration_error_logger);
            //access log for Sport Betting integration service
            if(file_exists($sportBettingIntegrationAccessPathFile)){
                if(filesize($sportBettingIntegrationAccessPathFile) >= $sportBettingIntegrationAccessLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($sportBettingIntegrationAccessPathFile, ".txt");
                    $file_path = dirname($sportBettingIntegrationAccessPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($sportBettingIntegrationAccessPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($sportBettingIntegrationAccessPathFile);
            $sport_betting_integration_access_logger = new Zend_Log();
            $sport_betting_integration_access_logger->addWriter($writerFile);
            Zend_Registry::set('sport_betting_integration_access_logger', $sport_betting_integration_access_logger);
        }catch(Exception $ex){
        }
    }

    //initialize External Integration loggers
    protected function _initExternalIntegrationLoggers(){
        $config = Zend_Registry::get('config');
        //Error log for external integration
        //external integration error log
        $writeExternalIntegrationErrorLogFile = $config->writeExternalIntegrationErrorLogFile;
        $externalIntegrationErrorPathFile = $config->externalIntegrationErrorPathFile;
        $externalIntegrationErrorLogSize = $config->externalIntegrationErrorLogSize;
        //external integration access log
        $writeExternalIntegrationAccessLogFile = $config->writeExternalIntegrationAccessLogFile;
        $externalIntegrationAccessPathFile = $config->externalIntegrationAccessPathFile;
        $externalIntegrationAccessLogSize = $config->externalIntegrationAccessLogSize;
        try{
            //EXTERNAL INTEGRATION LOG SETUP
            //error log for external integration
            if(file_exists($externalIntegrationErrorPathFile)){
                if(filesize($externalIntegrationErrorPathFile) >= $externalIntegrationErrorLogSize * 1024 * 1024){ //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($externalIntegrationErrorPathFile, ".txt");
                    $file_path = dirname($externalIntegrationErrorPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($externalIntegrationErrorPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($externalIntegrationErrorPathFile);
            $external_integration_error_logger = new Zend_Log();
            $external_integration_error_logger->addWriter($writerFile);
            Zend_Registry::set('external_integration_error_logger', $external_integration_error_logger);
            //access log for external integration
            if(file_exists($externalIntegrationAccessPathFile)){
                if(filesize($externalIntegrationAccessPathFile) >= $externalIntegrationAccessLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($externalIntegrationAccessPathFile, ".txt");
                    $file_path = dirname($externalIntegrationAccessPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($externalIntegrationAccessPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($externalIntegrationAccessPathFile);
            $external_integration_access_logger = new Zend_Log();
            $external_integration_access_logger->addWriter($writerFile);
            Zend_Registry::set('external_integration_access_logger', $external_integration_access_logger);
        }catch(Exception $ex){
        }
    }

    //initialize direct paysafecard integration loggers
    protected function _initPaysafecardDirectIntegrationLoggers(){
      $config = Zend_Registry::get('config');
      //Error log for external integration
      //external integration error log
      $writePaysafecardDirectIntegrationErrorLogFile = $config->writePaysafecardDirectIntegrationErrorLogFile;
      $paysafecardDirectIntegrationErrorPathFile = $config->paysafecardDirectIntegrationErrorPathFile;
      $paysafecardDirectIntegrationErrorLogSize = $config->paysafecardDirectIntegrationErrorLogSize;
      //paysafecardDirect integration access log
      $writePaysafecardDirectIntegrationAccessLogFile = $config->writePaysafecardDirectIntegrationAccessLogFile;
      $paysafecardDirectIntegrationAccessPathFile = $config->paysafecardDirectIntegrationAccessPathFile;
      $paysafecardDirectIntegrationAccessLogSize = $config->paysafecardDirectIntegrationAccessLogSize;
      //paysafecardDirect integration declined transactions log
      $writePaysafecardDirectIntegrationDeclinedTransactionsLogFile = $config->writePaysafecardDirectIntegrationDeclinedTransactionsLogFile;
      $paysafecardDirectIntegrationDeclinedTransactionsPathFile = $config->paysafecardDirectIntegrationDeclinedTransactionsPathFile;
      $paysafecardDirectIntegrationDeclinedTransactionsLogSize = $config->paysafecardDirectIntegrationDeclinedTransactionsLogSize;
      try{
          //PAYSAFECARD INTEGRATION LOG SETUP
          //error log for paysafecardDirect integration
          if(file_exists($paysafecardDirectIntegrationErrorPathFile)){
              if(filesize($paysafecardDirectIntegrationErrorPathFile) >= $paysafecardDirectIntegrationErrorLogSize * 1024 * 1024){ //rotate log error file if larger than errorLogSize MB in configuration
                  $file_name = basename($paysafecardDirectIntegrationErrorPathFile, ".txt");
                  $file_path = dirname($paysafecardDirectIntegrationErrorPathFile);
                  $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                  $new_file = $file_path . DS . $new_file_name;
                  rename($paysafecardDirectIntegrationErrorPathFile, $new_file);
              }
          }
          $writerFile = new Zend_Log_Writer_Stream($paysafecardDirectIntegrationErrorPathFile);
          $paysafecard_direct_integration_error_logger = new Zend_Log();
          $paysafecard_direct_integration_error_logger->addWriter($writerFile);
          Zend_Registry::set('paysafecard_direct_integration_error_logger', $paysafecard_direct_integration_error_logger);
          //access log for paysafecardDirect integration
          if(file_exists($paysafecardDirectIntegrationAccessPathFile)){
              if(filesize($paysafecardDirectIntegrationAccessPathFile) >= $paysafecardDirectIntegrationAccessLogSize * 1024 * 1024){
                  //rotate log error file if larger than errorLogSize MB in configuration
                  $file_name = basename($paysafecardDirectIntegrationAccessPathFile, ".txt");
                  $file_path = dirname($paysafecardDirectIntegrationAccessPathFile);
                  $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                  $new_file = $file_path . DS . $new_file_name;
                  rename($paysafecardDirectIntegrationAccessPathFile, $new_file);
              }
          }
          $writerFile = new Zend_Log_Writer_Stream($paysafecardDirectIntegrationAccessPathFile);
          $paysafecard_direct_integration_access_logger = new Zend_Log();
          $paysafecard_direct_integration_access_logger->addWriter($writerFile);
          Zend_Registry::set('paysafecard_direct_integration_access_logger', $paysafecard_direct_integration_access_logger);
          //declined transactions log for paysafecardDirect integration
          if(file_exists($paysafecardDirectIntegrationDeclinedTransactionsPathFile)){
              if(filesize($paysafecardDirectIntegrationDeclinedTransactionsPathFile) >= $paysafecardDirectIntegrationDeclinedTransactionsLogSize * 1024 * 1024){
                  //rotate log error file if larger than errorLogSize MB in configuration
                  $file_name = basename($paysafecardDirectIntegrationDeclinedTransactionsPathFile, ".txt");
                  $file_path = dirname($paysafecardDirectIntegrationDeclinedTransactionsPathFile);
                  $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                  $new_file = $file_path . DS . $new_file_name;
                  rename($paysafecardDirectIntegrationDeclinedTransactionsPathFile, $new_file);
              }
          }
          $writerFile = new Zend_Log_Writer_Stream($paysafecardDirectIntegrationDeclinedTransactionsPathFile);
          $paysafecard_direct_integration_declined_transactions_logger = new Zend_Log();
          $paysafecard_direct_integration_declined_transactions_logger->addWriter($writerFile);
          Zend_Registry::set('paysafecard_direct_integration_declined_transactions_logger', $paysafecard_direct_integration_declined_transactions_logger);
      }catch(Exception $ex){
      }
    }

    //initialize wirecard integration loggers
    protected function _initWirecardIntegrationLoggers(){
      $config = Zend_Registry::get('config');
      //Error log for external integration
      //external integration error log
      $writeWirecardIntegrationErrorLogFile = $config->writeWirecardIntegrationErrorLogFile;
      $wirecardIntegrationErrorPathFile = $config->wirecardIntegrationErrorPathFile;
      $wirecardIntegrationErrorLogSize = $config->wirecardIntegrationErrorLogSize;
      //wirecard integration access log
      $writeWirecardIntegrationAccessLogFile = $config->writeWirecardIntegrationAccessLogFile;
      $wirecardIntegrationAccessPathFile = $config->wirecardIntegrationAccessPathFile;
      $wirecardIntegrationAccessLogSize = $config->wirecardIntegrationAccessLogSize;
      //wirecard integration declined transactions log
      $writeWirecardIntegrationDeclinedTransactionsLogFile = $config->writeWirecardIntegrationDeclinedTransactionsLogFile;
      $wirecardIntegrationDeclinedTransactionsPathFile = $config->wirecardIntegrationDeclinedTransactionsPathFile;
      $wirecardIntegrationDeclinedTransactionsLogSize = $config->wirecardIntegrationDeclinedTransactionsLogSize;
      try{
          //EXTERNAL INTEGRATION LOG SETUP
          //error log for wirecard integration
          if(file_exists($wirecardIntegrationErrorPathFile)){
              if(filesize($wirecardIntegrationErrorPathFile) >= $wirecardIntegrationErrorLogSize * 1024 * 1024){ //rotate log error file if larger than errorLogSize MB in configuration
                  $file_name = basename($wirecardIntegrationErrorPathFile, ".txt");
                  $file_path = dirname($wirecardIntegrationErrorPathFile);
                  $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                  $new_file = $file_path . DS . $new_file_name;
                  rename($wirecardIntegrationErrorPathFile, $new_file);
              }
          }
          $writerFile = new Zend_Log_Writer_Stream($wirecardIntegrationErrorPathFile);
          $wirecard_integration_error_logger = new Zend_Log();
          $wirecard_integration_error_logger->addWriter($writerFile);
          Zend_Registry::set('wirecard_integration_error_logger', $wirecard_integration_error_logger);
          //access log for wirecard integration
          if(file_exists($wirecardIntegrationAccessPathFile)){
              if(filesize($wirecardIntegrationAccessPathFile) >= $wirecardIntegrationAccessLogSize * 1024 * 1024){
                  //rotate log error file if larger than errorLogSize MB in configuration
                  $file_name = basename($wirecardIntegrationAccessPathFile, ".txt");
                  $file_path = dirname($wirecardIntegrationAccessPathFile);
                  $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                  $new_file = $file_path . DS . $new_file_name;
                  rename($wirecardIntegrationAccessPathFile, $new_file);
              }
          }
          $writerFile = new Zend_Log_Writer_Stream($wirecardIntegrationAccessPathFile);
          $wirecard_integration_access_logger = new Zend_Log();
          $wirecard_integration_access_logger->addWriter($writerFile);
          Zend_Registry::set('wirecard_integration_access_logger', $wirecard_integration_access_logger);
          //declined transactions log for wirecard integration
          if(file_exists($wirecardIntegrationDeclinedTransactionsPathFile)){
              if(filesize($wirecardIntegrationDeclinedTransactionsPathFile) >= $wirecardIntegrationDeclinedTransactionsLogSize * 1024 * 1024){
                  //rotate log error file if larger than errorLogSize MB in configuration
                  $file_name = basename($wirecardIntegrationDeclinedTransactionsPathFile, ".txt");
                  $file_path = dirname($wirecardIntegrationDeclinedTransactionsPathFile);
                  $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                  $new_file = $file_path . DS . $new_file_name;
                  rename($wirecardIntegrationDeclinedTransactionsPathFile, $new_file);
              }
          }
          $writerFile = new Zend_Log_Writer_Stream($wirecardIntegrationDeclinedTransactionsPathFile);
          $wirecard_integration_declined_transactions_logger = new Zend_Log();
          $wirecard_integration_declined_transactions_logger->addWriter($writerFile);
          Zend_Registry::set('wirecard_integration_declined_transactions_logger', $wirecard_integration_declined_transactions_logger);
      }catch(Exception $ex){
      }
    }

    //initialize APCO merchant loggers
    protected function _initApcoIntegrationLoggers(){
        $config = Zend_Registry::get('config');
        //merchant error log
        $writeApcoIntegrationErrorLogFile = $config->writeApcoIntegrationErrorLogFile;
        $apcoIntegrationErrorPathFile = $config->apcoIntegrationErrorPathFile;
        $apcoIntegrationErrorLogSize = $config->apcoIntegrationErrorLogSize;
        //merchant access log
        $writeApcoIntegrationAccessLogFile = $config->writeApcoIntegrationAccessLogFile;
        $apcoIntegrationAccessPathFile = $config->apcoIntegrationAccessPathFile;
        $apcoIntegrationAccessLogSize = $config->apcoIntegrationAccessLogSize;
        //merchant declined transactions log
        $writeApcoIntegrationDeclinedTransactionsLogFile = $config->writeApcoIntegrationDeclinedTransactionsLogFile;
        $apcoIntegrationDeclinedTransactionsPathFile = $config->apcoIntegrationDeclinedTransactionsPathFile;
        $apcoIntegrationDeclinedTransactionsLogSize = $config->apcoIntegrationDeclinedTransactionsLogSize;
        try{
            //MERCHANT LOG SETUP
            //error log for merchant service
            if(file_exists($apcoIntegrationErrorPathFile)){
                if(filesize($apcoIntegrationErrorPathFile) >= $apcoIntegrationErrorLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($apcoIntegrationErrorPathFile, ".txt");
                    $file_path = dirname($apcoIntegrationErrorPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($apcoIntegrationErrorPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($apcoIntegrationErrorPathFile);
            $apco_integration_error_logger = new Zend_Log();
            $apco_integration_error_logger->addWriter($writerFile);
            Zend_Registry::set('apco_integration_error_logger', $apco_integration_error_logger);
            //access log for merchant service
            if(file_exists($apcoIntegrationAccessPathFile)){
                if(filesize($apcoIntegrationAccessPathFile) >= $apcoIntegrationAccessLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($apcoIntegrationAccessPathFile, ".txt");
                    $file_path = dirname($apcoIntegrationAccessPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($apcoIntegrationAccessPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($apcoIntegrationAccessPathFile);
            $apco_integration_access_logger = new Zend_Log();
            $apco_integration_access_logger->addWriter($writerFile);
            Zend_Registry::set('apco_integration_access_logger', $apco_integration_access_logger);
            //declined transactions log for merchant service
            if(file_exists($apcoIntegrationDeclinedTransactionsPathFile)){
                if(filesize($apcoIntegrationDeclinedTransactionsPathFile) >= $apcoIntegrationDeclinedTransactionsLogSize * 1024 * 1024){
                    //rotate log error file if larger than errorLogSize MB in configuration
                    $file_name = basename($apcoIntegrationDeclinedTransactionsPathFile, ".txt");
                    $file_path = dirname($apcoIntegrationDeclinedTransactionsPathFile);
                    $new_file_name = $file_name . "_" . date("d-M-Y_H-i-s") . ".txt";
                    $new_file = $file_path . DS . $new_file_name;
                    rename($apcoIntegrationDeclinedTransactionsPathFile, $new_file);
                }
            }
            $writerFile = new Zend_Log_Writer_Stream($apcoIntegrationDeclinedTransactionsPathFile);
            $apco_integration_declined_transactions_logger = new Zend_Log();
            $apco_integration_declined_transactions_logger->addWriter($writerFile);
            Zend_Registry::set('apco_integration_declined_transactions_logger', $apco_integration_declined_transactions_logger);

        }catch(Exception $ex){
        }
    }

    //initialize layout scripts path
    protected function _initLayout(){
        $layout = Zend_Layout::startMvc();
        $layout->setLayout('layout');
        return $layout;
    }

    //initialize view setup
    protected function _initView()
    {
        $view = Zend_Layout::getMvcInstance()->getView();
        return $view;
    }
}

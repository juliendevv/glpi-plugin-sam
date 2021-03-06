<?php
   /**
    * This function manage the installation of the plugin.
    *
    * @global object $DB
    * @param string $version
    */
   function pluginSAMInstall($version) {
      global $CFG_GLPI;
      global $DB;

      ini_set("memory_limit", "-1");
      ini_set("max_execution_time", "0");

      $migration = new Migration($version);

      $migration->displayMessage(__("Installation of plugin SAM"));

      /* Check existence of tables */
      $migration->displayMessage(__("Getting existence of glpi_deviceprocessors"));

      $tableDeviceProcessorsExist = TableExists('glpi_deviceprocessors');
      if (!$tableDeviceProcessorsExist){
         return false;
      }

      /* Clean database */
      $migration->displayMessage(__("Clean data from old installation of the plugin"));

      $migration->displayMessage(__("Dropping corefactor and pvu columns"));

      $query = "SELECT * FROM information_schema.columns WHERE table_schema = 'glpi' AND TABLE_NAME = 'glpi_deviceprocessors' AND column_name IN ( 'pvu' )";
      $res = $DB->queryOrDie($query, $DB->error());
      if ($res->num_rows > 0){
         $query = "ALTER TABLE glpi_deviceprocessors DROP pvu";
         $DB->queryOrDie($query, $DB->error());
      }

      $query = "SELECT * FROM information_schema.columns WHERE table_schema = 'glpi' AND TABLE_NAME = 'glpi_deviceprocessors' AND column_name IN ( 'plugin_sam_corefactors_id' )";
      $res = $DB->queryOrDie($query, $DB->error());
      if ($res->num_rows > 0){
         $query = "ALTER TABLE glpi_deviceprocessors DROP plugin_sam_corefactors_id";
         $DB->queryOrDie($query, $DB->error());
      }

      /* Create tables */
      $migration->displayMessage(__("Creation tables in database"));

      $migration->displayMessage(__("Creating glpi_plugin_sam_corefactors table"));

      if (!TableExists("glpi_plugin_sam_corefactors")){
         $query = "CREATE TABLE glpi_plugin_sam_corefactors (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `name` TEXT NOT NULL, `corefactor` FLOAT(11) NOT NULL)";
         $res = $DB->queryOrDie($query, $DB->error());
      }

      $migration->displayMessage(__("Creating glpi_plugin_sam_metrics table"));

      if (!TableExists("glpi_plugin_sam_metrics")){
         $query = "CREATE TABLE glpi_plugin_sam_metrics (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) NOT NULL, `classname` VARCHAR(255) NOT NULL)";
         $res = $DB->queryOrDie($query, $DB->error());
      }

      if (!TableExists("glpi_plugin_sam_ibm_pvu")){
         $query = "CREATE TABLE glpi_plugin_sam_ibm_pvu (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `desc` TEXT NOT NULL, `pvu` INT(11) NOT NULL)";
         $res = $DB->queryOrDie($query, $DB->error());
      }

      sleep(1);

      /* Modifying tables */
      $migration->displayMessage(__("Modifying existing tables in database"));


      $migration->displayMessage(__("Modifying glpi_deviceprocessors"));

      if (TableExists('glpi_deviceprocessors')){
         $query = 'ALTER TABLE glpi_deviceprocessors ADD (pvu INT(11), plugin_sam_corefactors_id INT(11))';
         $res = $DB->queryOrDie($query, $DB->error());
      }

      Plugin::registerClass('PluginSamCorefactor');

      return true;
   }
?>
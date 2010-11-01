<?php

  /**
   * This is the main backend file
   */

  include('../../php/app/inc/configuration.php');
  include('../../php/app/inc/dispatchhandler.php');

  $conf = new Configuration;
  $dispatch = new Dispatchhandler($conf);
  
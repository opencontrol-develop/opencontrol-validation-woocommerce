<?php
require_once ("config.php");
require_once ("constants.php");
require_once ("messages.php");
require_once ("status.php");
require_once (__DIR__. "/../utils/extra_information.php");
require_once (__DIR__. "/../utils/id_generator.php");
require_once (__DIR__. "/../utils/requests/validation.php");
require_once (__DIR__. "/../utils/http/client.php");
require_once (__DIR__. "/../services/validation_service.php");
require_once(__DIR__. "/../utils/entities/address.php");
require_once(__DIR__. "/../utils/entities/card.php");
require_once(__DIR__. "/../utils/entities/customer.php");
require_once(__DIR__. "/../utils/entities/merchant.php");
require_once(__DIR__. "/../utils/entities/product.php");
require_once(__DIR__. "/../utils/entities/payment.php");
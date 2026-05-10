<?php
include_once 'CONTROLLER/PlanController.php';

$controller = new PlanController();
$controller->handleRequest();
$pageSlug = isset($_GET['page']) ? $_GET['page'] : config::getSettings()['defaultPage'];
$controller->render($pageSlug);

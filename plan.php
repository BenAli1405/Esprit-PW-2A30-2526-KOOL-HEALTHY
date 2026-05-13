<?php
require_once __DIR__ . '/CONTROLLER/PlanController.php';
$controller = new PlanController();
$controller->handleRequest();
$pageSlug = isset($_GET['page']) ? $_GET['page'] : 'plan-backoffice';
$controller->render($pageSlug);

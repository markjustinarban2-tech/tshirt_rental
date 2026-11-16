<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once '../Class/RentalSystem.php';

$system = new RentalSystem();
$op = $_POST['op'] ?? '';

switch ($op) {
    case 'login':
        echo $system->login($_POST['username'], $_POST['pw']);
        break;
    case 'dashboard_stats':
        echo $system->getDashboardStats();
        break;
    case 'display_items':
        echo $system->displayItems();
        break;
    case 'add_item':
        // Receives text parameters and $_FILES
        echo $system->addItemWithImage(
            $_POST['category_id'],
            $_POST['name'],
            $_POST['size'],
            $_POST['color'],
            $_POST['price'],
            $_FILES
        );
        break;
    case 'delete_item':
        echo $system->deleteItem($_POST['id']);
        break;
    case 'place_order':
        echo $system->placeOrder($_POST['username'], $_POST['item_id'], $_POST['rental_days'], $_POST['total_price']);
        break;
    default:
        echo json_encode(['error' => 'Invalid operation']);
        break;
}
?>
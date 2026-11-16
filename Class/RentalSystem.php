<?php
require_once 'Database.php';

class RentalSystem extends Database {

    // 1. User Login
    public function login($username, $pass) {
        $sql = "SELECT * FROM users WHERE username=? AND Password=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $username, $pass);
        $stmt->execute();
        $data = $stmt->get_result();

        if ($row = $data->fetch_assoc()) {
            return json_encode([
                'success' => true,
                'message' => 'Login Successful!',
                'username' => $row['username'],
                'Role' => $row['Role'],
                'FullName' => $row['FullName']
            ]);
        } else {
            return json_encode([
                'success' => false,
                'message' => 'Invalid Credentials'
            ]);
        }
    }

    // 2. Admin Dashboard Stats
    public function getDashboardStats() {
        $total = $this->conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
        $new = $this->conn->query("SELECT COUNT(*) as count FROM orders WHERE status='New'")->fetch_assoc()['count'];
        $packed = $this->conn->query("SELECT COUNT(*) as count FROM orders WHERE status='Packed'")->fetch_assoc()['count'];
        $dispatched = $this->conn->query("SELECT COUNT(*) as count FROM orders WHERE status='Dispatched'")->fetch_assoc()['count'];

        return json_encode([
            'total_orders' => $total,
            'new_orders' => $new,
            'packed_orders' => $packed,
            'dispatched_orders' => $dispatched
        ]);
    }

    // 3. Display All Items (Includes full image URL for Flutter)
    public function displayItems() {
        $sql = "SELECT i.*, c.category_name FROM items i JOIN categories c ON i.category_id = c.id ORDER BY i.id DESC";
        $res = $this->conn->query($sql);
        $items = [];
        $baseUrl = "http://10.0.2.2/tshirt_rental/uploads/"; 

        while ($row = $res->fetch_assoc()) {
            $row['image_url'] = $baseUrl . (!empty($row['image']) ? $row['image'] : 'default.png');
            $items[] = $row;
        }
        return json_encode($items);
    }

    // 4. Add Item with Image Upload
    public function addItemWithImage($category_id, $name, $size, $color, $price, $file) {
        $imageName = "default.png";

        if (isset($file['image']) && $file['image']['error'] == 0) {
            $targetDir = "../uploads/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $ext = pathinfo($file['image']['name'], PATHINFO_EXTENSION);
            $imageName = time() . "_" . uniqid() . "." . $ext;
            $targetFilePath = $targetDir . $imageName;

            move_uploaded_file($file['image']['tmp_name'], $targetFilePath);
        }

        $sql = "INSERT INTO items (category_id, name, size, color, price_per_day, image) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssds", $category_id, $name, $size, $color, $price, $imageName);
        $success = $stmt->execute();

        return json_encode(['success' => $success, 'image' => $imageName]);
    }

    // 5. Delete Item (Admin)
    public function deleteItem($id) {
        $sql = "DELETE FROM items WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        return json_encode(['success' => $success]);
    }

    // 6. Place Order (Customer)
    public function placeOrder($username, $item_id, $rental_days, $total_price) {
        $sql = "INSERT INTO orders (username, item_id, rental_days, total_price, status) VALUES (?, ?, ?, ?, 'New')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siid", $username, $item_id, $rental_days, $total_price);
        $success = $stmt->execute();
        return json_encode(['success' => $success]);
    }
}
?>
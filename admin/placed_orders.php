<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;  // Use null coalescing for safety

if (!$admin_id) {
    header('location: admin_login.php');
    exit();  // Always exit after header redirect
}

if (isset($_POST['update_payment'])) {
    $order_id = $_POST['order_id'] ?? null;
    $payment_status = $_POST['payment_status'] ?? null;

    // Validate inputs
    if ($order_id && in_array($payment_status, ['pending', 'completed'])) {
        $update_status = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
        if ($update_status->execute([$payment_status, $order_id])) {
            $message[] = 'Payment status updated!';
        } else {
            $message[] = 'Failed to update payment status.';
        }
    } else {
        $message[] = 'Invalid input for payment update.';
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'] ?? null;

    // Validate input
    if ($delete_id) {
        $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
        if ($delete_order->execute([$delete_id])) {
            header('location: placed_orders.php');
            exit();
        } else {
            $message[] = 'Failed to delete order.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Placed Orders</title>

   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<!-- Placed Orders Section -->
<section class="placed-orders">
   <h1 class="heading">Placed Orders</h1>

   <div class="box-container">
   <?php
      $select_orders = $conn->prepare("SELECT * FROM `orders`");
      $select_orders->execute();
      if ($select_orders->rowCount() > 0) {
         while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
   ?>
   <div class="box">
      <p>User ID: <span><?= htmlspecialchars($fetch_orders['user_id'] ?? 'N/A'); ?></span></p>
      <p>Placed On: <span><?= htmlspecialchars($fetch_orders['placed_on'] ?? 'N/A'); ?></span></p>
      <p>Name: <span><?= htmlspecialchars($fetch_orders['name'] ?? 'N/A'); ?></span></p>
      <p>Email: <span><?= htmlspecialchars($fetch_orders['email'] ?? 'N/A'); ?></span></p>
      <p>Number: <span><?= htmlspecialchars($fetch_orders['number'] ?? 'N/A'); ?></span></p>
      <p>Address: <span><?= htmlspecialchars($fetch_orders['address'] ?? 'N/A'); ?></span></p>
      <p>Total Products: <span><?= htmlspecialchars($fetch_orders['total_products'] ?? 'N/A'); ?></span></p>
      <p>Total Price: <span>$<?= htmlspecialchars($fetch_orders['total_price'] ?? '0.00'); ?>/-</span></p>
      <p>Payment Method: <span><?= htmlspecialchars($fetch_orders['method'] ?? 'N/A'); ?></span></p>
      <form action="" method="POST">
         <input type="hidden" name="order_id" value="<?= htmlspecialchars($fetch_orders['id'] ?? ''); ?>">
         <select name="payment_status" class="drop-down">
            <option value="" selected disabled><?= htmlspecialchars($fetch_orders['payment_status'] ?? 'unknown'); ?></option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
         </select>
         <div class="flex-btn">
            <input type="submit" value="Update" class="btn" name="update_payment">
            <a href="placed_orders.php?delete=<?= htmlspecialchars($fetch_orders['id'] ?? ''); ?>" class="delete-btn" onclick="return confirm('Delete this order?');">Delete</a>
         </div>
      </form>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">No orders placed yet!</p>';
      }
   ?>
   </div>
</section>

<!-- Custom JS -->
<script src="../js/admin_script.js"></script>

</body>
</html>

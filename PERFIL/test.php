<?php
$message = "";
$status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);

    if (empty($username)) {
        $status = "danger";
        $message = "The username field cannot be left blank.";
    } else {
        $status = "success";
        $message = "Welcome back, " . htmlspecialchars($username) . "!";
    }
}
?>

<!-- HTML Markup Below -->
<div class="container mt-4">
    <!-- Render the alert only if a message exists -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $status; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-items form-control">
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
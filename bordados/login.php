<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = intval($_POST['usuario_id'] ?? 0);
    
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['rol'] = $user['rol'];
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Usuario no válido";
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Selección de Usuario</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .login-container { max-width: 400px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: #fff; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; margin-top: 10px; }
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Seleccione su usuario</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label>Usuario:</label>
                <select name="usuario_id" required>
                    <option value="">-- Seleccione --</option>
                    <?php
                    $usuarios = $conn->query("SELECT id, username, nombre_completo FROM usuarios");
                    while ($usuario = $usuarios->fetch_assoc()):
                    ?>
                        <option value="<?= $usuario['id'] ?>">
                            <?= htmlspecialchars($usuario['nombre_completo']) ?> (<?= $usuario['username'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>
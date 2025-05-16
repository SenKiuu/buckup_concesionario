<?php
include("includes/conexion.php");
$id = (int) $_GET['id'];
$result = $conn->query("SELECT modelo, precio FROM coches WHERE id = $id");
echo json_encode($result->fetch_assoc());

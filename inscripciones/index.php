<?php
session_start();
if (!isset($_SESSION['club_id'])) {
    header("Location: login.php");
    exit;
}
header("Location: panel_club.php");
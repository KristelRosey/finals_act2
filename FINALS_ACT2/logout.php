<style>
    body {
        font-family: Arial;
        background: #f4f4f4;
        padding: 30px;
    }
    form {
        background: white;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        box-shadow: 0 0 8px #ccc;
    }
    input, textarea, button {
        width: 100%;
        margin: 10px 0;
        padding: 10px;
    }
    button {
        background: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
    }
</style>



<?php
session_start();
session_destroy();
header("Location: login.php");
exit();

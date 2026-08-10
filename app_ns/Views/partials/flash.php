<?php
$flash = \App\Core\Session::get('flash');
if ($flash) {
    \App\Core\Session::remove('flash');
    $type = htmlspecialchars($flash['type'] ?? 'success');
    $msg  = htmlspecialchars($flash['message'] ?? '');
    echo "<div class=\"flash {$type}\">{$msg}</div>";
}

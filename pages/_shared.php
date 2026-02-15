<?php
/**
 * Shared Templates - Reduce Code Duplication
 */

// Page header wrapper
function pageHeader($title, $icon = 'information', $iconColor = '00AA13') {
    echo '<div class="container" style="max-width:900px">';
    echo '<h1 style="text-align:center;margin-bottom:2rem">';
    echo '<img src="https://api.iconify.design/mdi/'.$icon.'.svg?color=%23'.$iconColor.'" ';
    echo 'style="width:60px;height:60px;display:block;margin:0 auto 1rem" alt="">';
    echo H::s($title);
    echo '</h1>';
}

// Simple page header (no icon)
function simpleHeader($title) {
    echo '<div class="container">';
    echo '<h1 style="margin-bottom:2rem">'.H::s($title).'</h1>';
}

// Info card start
function cardStart($title, $icon = null) {
    echo '<div class="info-card">';
    if ($title) {
        echo '<h2>';
        if ($icon) {
            echo '<img src="https://api.iconify.design/mdi/'.$icon.'.svg" class="icon" alt="">';
        }
        echo H::s($title);
        echo '</h2>';
    }
}

// Info card end
function cardEnd() {
    echo '</div>';
}

// Empty state
function emptyState($icon, $message) {
    echo '<div class="info-card" style="text-align:center;padding:3rem">';
    echo '<img src="https://api.iconify.design/mdi/'.$icon.'.svg?color=%2394a3b8" style="width:80px;height:80px" alt="">';
    echo '<p style="color:var(--text-secondary);margin-top:1rem">'.H::s($message).'</p>';
    echo '</div>';
}

// Info grid item
function infoItem($label, $value) {
    echo '<div class="info-item">';
    echo '<div class="info-label">'.H::s($label).'</div>';
    echo '<div class="info-value">'.H::s($value).'</div>';
    echo '</div>';
}

// Form group
function formGroup($label, $type, $name, $value = '', $required = false, $disabled = false) {
    echo '<div class="form-group">';
    echo '<label class="form-label">'.H::s($label).($required?' *':'').'</label>';
    
    if ($type === 'select') {
        echo '<select name="'.$name.'"'.($required?' required':'').($disabled?' disabled':'').'>';
        echo $value; // Value contains options HTML
        echo '</select>';
    } elseif ($type === 'textarea') {
        echo '<textarea name="'.$name.'" rows="3"'.($required?' required':'').($disabled?' disabled':'').'>'.H::s($value).'</textarea>';
    } else {
        echo '<input type="'.$type.'" name="'.$name.'" value="'.H::s($value).'"'.($required?' required':'').($disabled?' disabled':'').'>';
    }
    
    echo '</div>';
}

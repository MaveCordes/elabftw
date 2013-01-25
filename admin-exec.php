<?php
/********************************************************************************
*                                                                               *
*   Copyright 2012 Nicolas CARPi (nicolas.carpi@gmail.com)                      *
*   http://www.elabftw.net/                                                     *
*                                                                               *
********************************************************************************/

/********************************************************************************
*  This file is part of eLabFTW.                                                *
*                                                                               *
*    eLabFTW is free software: you can redistribute it and/or modify            *
*    it under the terms of the GNU Affero General Public License as             *
*    published by the Free Software Foundation, either version 3 of             *
*    the License, or (at your option) any later version.                        *
*                                                                               *
*    eLabFTW is distributed in the hope that it will be useful,                 *
*    but WITHOUT ANY WARRANTY; without even the implied                         *
*    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR                    *
*    PURPOSE.  See the GNU Affero General Public License for more details.      *
*                                                                               *
*    You should have received a copy of the GNU Affero General Public           *
*    License along with eLabFTW.  If not, see <http://www.gnu.org/licenses/>.   *
*                                                                               *
********************************************************************************/
/* admin-exec.php - for administration of the elab */
require_once('inc/common.php');
if ($_SESSION['is_admin'] != 1) {die('You are not admin !');} // only admin can use this
$msg_arr = array();

// VALIDATE USERS
if (isset($_POST['validate'])) {
    $sql = "UPDATE users SET validated = 1 WHERE userid = :userid";
    $req = $bdd->prepare($sql);
    foreach ($_POST['validate'] as $user) {
        $req->execute(array(
            'userid' => $user
        ));
            $msg_arr[] = 'Validated user with user ID : '.$user;
    }
    $_SESSION['infos'] = $msg_arr;
    header('Location: admin.php');
    exit();
}

// MANAGE USERS
// called from ajax
if (isset($_POST['deluser']) && is_pos_int($_POST['deluser'])) {
    $userid = $_POST['deluser'];
    $msg_arr = array();
    // DELETE USER
    $sql = "DELETE FROM users WHERE userid = ".$userid;
    $req = $bdd->prepare($sql);
    $req->execute();
    $sql = "DELETE FROM experiments_tags WHERE userid = ".$userid;
    $req = $bdd->prepare($sql);
    $req->execute();
    $sql = "DELETE FROM experiments WHERE userid = ".$userid;
    $req = $bdd->prepare($sql);
    $req->execute();
    // get all filenames
    $sql = "SELECT long_name FROM uploads WHERE userid = :userid AND type = :type";
    $req = $bdd->prepare($sql);
    $req->execute(array(
        'userid' => $userid,
        'type' => 'exp'
    ));
    while($uploads = $req->fetch()){
        // Delete file
        $filepath = $ini_arr['upload_dir'].$uploads['long_name'];
        unlink($filepath);
    }
    $sql = "DELETE FROM uploads WHERE userid = ".$userid;
    $req = $bdd->prepare($sql);
    $req->execute();
    $msg_arr[] = 'Everything was purged successfully.';
    $_SESSION['infos'] = $msg_arr;
}

// ITEMS TYPES
if (isset($_POST['item_type_name']) && is_pos_int($_POST['item_type_id'])) {
    $item_type_id = $_POST['item_type_id'];
    $item_type_name = filter_var($_POST['item_type_name'], FILTER_SANITIZE_STRING); 
    // we remove the # of the hexacode and sanitize string
    $item_type_bgcolor = filter_var(substr($_POST['item_type_bgcolor'], 1, 6), FILTER_SANITIZE_STRING);
    $item_type_template = check_body($_POST['item_type_template']);
    //TODO
    $item_type_tags = '';
    $sql = "UPDATE items_types SET name = :name, bgcolor = :bgcolor , template = :template, tags = :tags WHERE id = :id";
    $req = $bdd->prepare($sql);
    $result = $req->execute(array(
        'name' => $item_type_name,
        'bgcolor' => $item_type_bgcolor,
        'template' => $item_type_template,
        'tags' => $item_type_tags,
        'id' => $item_type_id
    ));
    if ($result){
        $msg_arr[] = 'New item category updated successfully.';
        $_SESSION['infos'] = $msg_arr;
        header('Location: admin.php#items_types');
        exit();
    } else { //sql fail
        $msg_arr[] = 'There was a problem in the SQL request. Report a bug !';
        $_SESSION['errors'] = $msg_arr;
        header('Location: admin.php');
        exit();
    }
}
// add new item type
if (isset($_POST['new_item_type']) && is_pos_int($_POST['new_item_type'])) {
    $item_type_name = filter_var($_POST['new_item_type_name'], FILTER_SANITIZE_STRING); 
    // we remove the # of the hexacode and sanitize string
    $item_type_bgcolor = filter_var(substr($_POST['new_item_type_bgcolor'], 1, 6), FILTER_SANITIZE_STRING);
    $item_type_template = check_body($_POST['new_item_type_template']);
    //TODO
    $item_type_tags = '';
    $sql = "INSERT INTO items_types(name, bgcolor, template, tags) VALUES(:name, :bgcolor, :template, :tags)";
    $req = $bdd->prepare($sql);
    $result = $req->execute(array(
        'name' => $item_type_name,
        'bgcolor' => $item_type_bgcolor,
        'template' => $item_type_template,
        'tags' => $item_type_tags
    ));
    if ($result){
        $msg_arr[] = 'New item category added successfully.';
        $_SESSION['infos'] = $msg_arr;
        header('Location: admin.php#items_types');
        exit();
    } else { //sql fail
        $msg_arr[] = 'There was a problem in the SQL request. Report a bug !';
        $_SESSION['errors'] = $msg_arr;
        header('Location: admin.php');
        exit();
    }
}
?>


<?php
/****************************************************************/
/* ATutor														*/
/****************************************************************/
/* Copyright (c) 2002-2010                                      */
/* Inclusive Design Institute                                   */
/* http://atutor.ca												*/
/*                                                              */
/* This program is free software. You can redistribute it and/or*/
/* modify it under the terms of the GNU General Public License  */
/* as published by the Free Software Foundation.				*/
/****************************************************************/
// $Id$

define('AT_INCLUDE_PATH', '../../../include/');
require(AT_INCLUDE_PATH.'vitals.inc.php');

// authenticate ot+oid..
$owner_type = abs($_REQUEST['ot']);
$owner_id = abs($_REQUEST['oid']);
if (!($owner_status = blogs_authenticate($owner_type, $owner_id)) || !query_bit($owner_status, BLOGS_AUTH_WRITE)) {
	$msg->addError('ACCESS_DENIED');
	header('Location: index.php');
	exit;
}

$id = abs($_REQUEST['id']);
$delete_id = abs($_REQUEST['delete_id']);

$sql = "SELECT post_id FROM %sblog_posts WHERE owner_type=%d AND owner_id=%d AND post_id=%d";
$row_posts = queryDB($sql, array(TABLE_PREFIX, $owner_type, $owner_id, $id));

if(count($row_posts) == 0){
	$msg->addError('ACCESS_DENIED');
	header('Location: index.php');
	exit;
}

if (isset($_POST['submit_no'])) {
	$msg->addFeedback('CANCELLED');
	header('Location: '.url_rewrite('mods/_standard/blogs/post.php?ot='.$owner_type.SEP.'oid='.$owner_id.SEP.'id='.$id, AT_PRETTY_URL_IS_HEADER));
	exit;
} else if (isset($_POST['submit_yes'])) {

	$sql = "DELETE FROM %sblog_posts_comments WHERE comment_id=%d AND post_id=%d";
	$result = queryDB($sql, array(TABLE_PREFIX, $delete_id, $id));	
	if($result == 1){
		$sql = "UPDATE %sblog_posts SET num_comments=num_comments-1, date=date WHERE owner_type=%d AND owner_id=%d AND post_id=%d";
		$result = queryDB($sql, array(TABLE_PREFIX, $owner_type, $owner_id, $id));
	}

	$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
	header('Location: '.url_rewrite('mods/_standard/blogs/post.php?ot='.$owner_type.SEP.'oid='.$owner_id.SEP.'id='.$id, AT_PRETTY_URL_IS_HEADER));
	exit;
}

require(AT_INCLUDE_PATH.'header.inc.php');

$hidden_vars = array('id' => $id, 'ot' => $owner_type, 'oid' => $owner_id, 'delete_id' => $delete_id);
//get the comment to print into the confirm box
$sql = 'SELECT comment FROM %sblog_posts_comments WHERE comment_id=%d';
$row = queryDB($sql, array(TABLE_PREFIX, $delete_id), TRUE);

$msg->addConfirm(array('DELETE', AT_print($row['comment'], 'blog_posts_comments.comment')), $hidden_vars);
$msg->printConfirm();

require(AT_INCLUDE_PATH.'footer.inc.php');
?>
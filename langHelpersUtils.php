<?php

//Accounts must have unique emails, so this function checks
//  if a given email is already in the system
function checkEmail($email)
{
    $conn = connectToDB();
    $query = "SELECT * FROM User WHERE userEmail = '".$email."';";
    $result = mysqli_query($conn, $query);
    $emailExists = false;
    
    if(mysqli_num_rows($result) > 0)
    {
        $emailExists = true;
    }
    
    return $emailExists;
}

//Accounts must use unique nicknames, so this function
//  checks if a given nickname is already in the system
function checkNickname($nickname)
{
    $conn = connectToDB();
    $query = "SELECT * FROM User WHERE userNickname = '".$nickname."';";
    $result = mysqli_query($conn, $query);
    $nicknameExists = false;
    
    if(mysqli_num_rows($result) > 0)
    {
        $nicknameExists = true;
    }
    
    return $nicknameExists;
}

//Inserts a new user to the database
function createAccount($email, $nickname, $password, $firstName, $lastName, $hash)
{
    $conn = connectToDB();
    $query = "INSERT INTO User values (null, '".$email."', '".$nickname."', '".$password."', '".$firstName."', '".$lastName."', '".$hash."', 0, 0);";
    mysqli_query($conn, $query);
}

function checkUnactivatedAccount($email, $hash)
{
    $conn = connectToDB();
    $query = "SELECT userEmail, hash, active FROM User WHERE userEmail = '".$email."' AND hash = '".$hash."' AND active = 0;";
    $check = mysqli_query($conn, $query);
    $match = mysqli_num_rows($check);
    return $match;
}

function updateAccountActivation($email, $hash)
{
    $conn = connectToDB();
    $query = "UPDATE User SET active = 1 WHERE userEmail = '".$email."' AND hash = '".$hash."' AND active = 0;";
    mysqli_query($conn, $query);
}

function checkUser($email, $password)
{
    $conn = connectToDB();
    $safeLogIn = true;
    $query = "SELECT userPassword, active FROM User WHERE userEmail = '".$email."';";
    $result = mysqli_query($conn, $query);
    if(mysqli_num_rows($result) == 0)
    {
        $safeLogIn = false;
    }
    else
    {
        $row = mysqli_fetch_assoc($result);
        if($row['active'] == 0 || $row['userPassword'] != $password)
        {
            $safeLogIn = false;
        }
    }
    return $safeLogIn;
}

function userChoseFirstLanguage($userID)
{
    $conn = connectToDB();
    $choseLanguage = true;
    $query = "SELECT pickedLanguage FROM User WHERE userID = '".$userID."';";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    if($row['pickedLanguage'] == 0)
    {
        $choseLanguage = false;
    }
    return $choseLanguage;
}

function getAllLanguages()
{
    $conn = connectToDB();
    $languages = array();
    $query = "SELECT languageName FROM Language;";
    $result = mysqli_query($conn, $query);
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_assoc($result))
		{
		    $language = $row["languageName"];
            array_push($languages, $language);
		}
	}
	return $languages;
}

function setUserLanguage($userID, $languageID)
{
    $conn = connectToDB();
    $setLangQuery = "";
    $firstLangQuery = "SELECT pickedLanguage FROM User WHERE userID = '".$userID."';";
    $firstLangResult = mysqli_query($conn, $firstLangQuery);
    $firstLangRow = mysqli_fetch_assoc($firstLangResult);
    if($firstLangRow['pickedLanguage'] == 0)
    {
        $setLangQuery = "INSERT INTO UserLanguage VALUES ('".$userID."', '".$languageID."');";
        $updatePickedQuery = "UPDATE User SET pickedLanguage = 1 WHERE userID = '".$userID."';";
        mysqli_query($conn, $updatePickedQuery);
    }
    else if($firstLangRow['pickedLanguage'] == 1)
    {
        $setLangQuery = "UPDATE UserLanguage SET languageID = '".$languageID."' WHERE userID = '".$userID."';";
    }
    mysqli_query($conn, $setLangQuery);
}

function getUserID($email)
{
    $conn = connectToDB();
    $query = "SELECT userID FROM User WHERE userEmail = '".$email."';";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $id = $row['userID'];
    return $id;
}

function getUserLanguageID($id)
{
    $conn = connectToDB();
    $query = "SELECT languageID FROM UserLanguage WHERE userID = '".$id."';";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $id = $row['languageID'];
    return $id;
}

function getLanguageName($langID)
{
    $conn = connectToDB();
    $query = "SELECT languageName FROM Language WHERE languageID = '".$langID."';";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $langName = $row['languageName'];
    return $langName;
}

function createQuestion($title, $content, $langID, $userID)
{
    $conn = connectToDB();
    date_default_timezone_set('America/Chicago');
    $currentDateTime = date('Y/m/d h:i:s a');
    $queryQuestion = "INSERT INTO Question VALUES (null, '".$title."', '".$content."', '".$currentDateTime."', 0, '".$langID."');";
    mysqli_query($conn, $queryQuestion);
    
    $questionID = mysqli_insert_id($conn);
    $queryLink = "INSERT INTO UserQuestion VALUES ('".$userID."', '".$questionID."');";
    mysqli_query($conn, $queryLink);
}

function getAllUserPosts($userID)
{
    $conn = connectToDB();
    $postIDs = array();
    $query = "SELECT questionID FROM UserQuestion WHERE userID = '".$userID."';";
    $result = mysqli_query($conn, $query);
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_assoc($result))
		{
		    $id = intval($row["questionID"]);
            array_push($postIDs, $id);
		}
	}
	return $postIDs;
}

function getPostInfo($postID)
{
    $conn = connectToDB();
    $postInfo = array();
    $query = "SELECT questionTitle, questionDateTime, languageID, closed FROM Question WHERE questionID = '".$postID."';";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $postInfo['title'] = $row['questionTitle'];
    $postInfo['datetime'] = $row['questionDateTime'];

    $langID = $row['languageID'];
    $language = getLanguageName($langID);
    $postInfo['language'] = $language;
	
	$closed = "";
	if($row['closed'] == 0)
	{
		$closed = "Open";
	}
	else
	{
		$closed = "Closed";
	}
	$postInfo['closed'] = $closed;
    return $postInfo;
}

function getQuestion($postID)
{
	$conn = connectToDB();
    $questionInfo = array();
    $query = "SELECT questionTitle, questionDateTime, questionContent FROM Question WHERE questionID = '".$postID."';";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $questionInfo['title'] = $row['questionTitle'];
    $questionInfo['datetime'] = $row['questionDateTime'];
	$questionInfo['content'] = $row['questionContent'];
	
	$query2 = "SELECT userID FROM UserQuestion WHERE questionID = '".$postID."';";
	$result2 = mysqli_query($conn, $query2);
	$row2 = mysqli_fetch_assoc($result2);
	$nickname = getUserNickname($row2['userID']);
	$questionInfo['userNickname'] = $nickname;
	
    return $questionInfo;
}

function checkIfOpen($postID)
{
    $isOpen = true;
    
    $conn = connectToDB();
    $query = "SELECT closed FROM Question WHERE questionID ='".$postID."';";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    if($row['closed'] == 1)
    {
        $isOpen = false;
    }
    
    return $isOpen;
}

function closePost($postID)
{
    $conn = connectToDB();
    $query = "UPDATE Question SET closed = 1 WHERE questionID = '".$postID."';";
    mysqli_query($conn, $query);
}

function getAllPosts($langID)
{
	$conn = connectToDB();
    $postIDs = array();
    $query = "SELECT questionID FROM Question WHERE languageID = '".$langID."' AND closed = 0;";
    $result = mysqli_query($conn, $query);
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_assoc($result))
		{
		    $id = intval($row["questionID"]);
            array_push($postIDs, $id);
		}
	}
	return $postIDs;
}

function getUserNickname($userID)
{
	$conn = connectToDB();
	$query = "SELECT userNickname FROM User WHERE userID = '".$userID."';";
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_assoc($result);
	$userNickname = $row['userNickname'];
	return $userNickname;
}

?>
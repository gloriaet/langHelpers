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
    $query = "INSERT INTO User values (null, '".$email."', '".$nickname."', '".$password."', '".$firstName."', '".$lastName."', '".$hash."', 0, 0, 0);";
    mysqli_query($conn, $query);
}

//Checks if a particular account is in the database and if it's inactive
//Returns 1 if specified account is in the system and is inactive
//Returns 0 in all other cases, prevents verify account page from being manipulated (only use of GET arguments in system)
function checkUnactivatedAccount($email, $hash)
{
    $conn = connectToDB();
    $query = "SELECT userEmail, hash, active FROM User WHERE userEmail = '".$email."' AND hash = '".$hash."' AND active = 0;";
    $check = mysqli_query($conn, $query);
    $match = mysqli_num_rows($check);
    return $match;
}

//Activates a particular account, sets active row to 1 for user
function updateAccountActivation($email, $hash)
{
    $conn = connectToDB();
    $query = "UPDATE User SET active = 1 WHERE userEmail = '".$email."' AND hash = '".$hash."' AND active = 0;";
    mysqli_query($conn, $query);
}

//Authentication for Normal User log in attempts
function checkUser($email, $password)
{
    $conn = connectToDB();
    $safeLogIn = true; //indicates proper log in
    $query = "SELECT userPassword, active, banned FROM User WHERE userEmail = '".$email."';";
    $result = mysqli_query($conn, $query);
    if(mysqli_num_rows($result) == 0) //this account does not exist, no log in
    {
        $safeLogIn = false;
    }
    else
    {
        $row = mysqli_fetch_assoc($result);
        if($row['active'] == 0 || $row['banned'] == 1 || $row['userPassword'] != $password) //improper log in - account inactive, banned, or password invalid
        {
            $safeLogIn = false;
        }
    }
    return $safeLogIn;
}

//Authentication for Moderator log in attempts
function checkModerator($email, $password)
{
    $conn = connectToDB();
    $safeLogIn = true; //indicates proper log in
    $query = "SELECT modPassword FROM Moderator WHERE modEmail = '".$email."';";
    $result = mysqli_query($conn, $query);
    if(mysqli_num_rows($result) == 0) //this account does not exist, no log in
    {
        $safeLogIn = false;
    }
    else
    {
        $row = mysqli_fetch_assoc($result);
        if($row['modPassword'] != $password) //improper log in - password invalid
        {
            $safeLogIn = false;
        }
    }
    return $safeLogIn;
}

//Checks if user has logged in for the first time - at first log in, user chooses desired language
function userChoseFirstLanguage($userID)
{
    $conn = connectToDB();
    $choseLanguage = true; //user has already chosen a language
    $query = "SELECT pickedLanguage FROM User WHERE userID = '".$userID."';";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    if($row['pickedLanguage'] == 0) //first time log in - need to pick first language
    {
        $choseLanguage = false;
    }
    return $choseLanguage;
}

//Retrieves all language choices available
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

//Sets a user's chosen language both for the first time and for any subsequent changes
function setUserLanguage($userID, $languageID)
{
    $conn = connectToDB();
    $setLangQuery = "";
    $firstLangQuery = "SELECT pickedLanguage FROM User WHERE userID = '".$userID."';";
    $firstLangResult = mysqli_query($conn, $firstLangQuery);
    $firstLangRow = mysqli_fetch_assoc($firstLangResult);
    if($firstLangRow['pickedLanguage'] == 0) //this is the first time the user has chosen a language
    {
        $setLangQuery = "INSERT INTO UserLanguage VALUES ('".$userID."', '".$languageID."');";
        $updatePickedQuery = "UPDATE User SET pickedLanguage = 1 WHERE userID = '".$userID."';";
        mysqli_query($conn, $updatePickedQuery);
    }
    else if($firstLangRow['pickedLanguage'] == 1) //this is a subsequent change of language for the user
    {
        $setLangQuery = "UPDATE UserLanguage SET languageID = '".$languageID."' WHERE userID = '".$userID."';";
    }
    mysqli_query($conn, $setLangQuery);
}

//Retrieves the unique userID associated with a particular user based on their unique email address
function getUserID($email)
{
    $conn = connectToDB();
    $query = "SELECT userID FROM User WHERE userEmail = '".$email."';";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $id = $row['userID'];
    return $id;
}

//Retrieves the unique userID associated with a particular user based on their unique nickname
function getUserIDByNickname($nickname)
{
    $conn = connectToDB();
    $query = "SELECT userID FROM User WHERE userNickname = '".$nickname."';";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $id = $row['userID'];
    return $id;
}

//Retrieves the languageID of the language the user is currently working with
function getUserLanguageID($id)
{
    $conn = connectToDB();
    $query = "SELECT languageID FROM UserLanguage WHERE userID = '".$id."';";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $id = $row['languageID'];
    return $id;
}

//Retrieves the language name associated with a particular languageID
function getLanguageName($langID)
{
    $conn = connectToDB();
    $query = "SELECT languageName FROM Language WHERE languageID = '".$langID."';";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $langName = $row['languageName'];
    return $langName;
}

//Adds a question that a user has submitted to the database
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

//Adds to the database an answer to a particular question that a user has submitted
function createAnswer($content, $userID, $postID)
{
	$conn = connectToDB();
	date_default_timezone_set('America/Chicago');
	$currentDateTime = date('Y/m/d h:i:s a');
	$queryAnswer = "INSERT INTO Answer VALUES (null, '".$content."', '".$currentDateTime."', 0, '".$postID."');";
	mysqli_query($conn, $queryAnswer);

	$answerID = mysqli_insert_id($conn);
	$queryLink = "INSERT INTO UserAnswer VALUES ('".$userID."', '".$answerID."');";
	mysqli_query($conn, $queryLink);
}

//Retrieves all of the questionIDs for the questions that a particular user has submitted to the system
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

//Retrieves information about a particular question to display in tables
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

//Retrieves detailed information about a particular question to display on its own page
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

//Checks if a particular question has been closed by the user that posted it
function checkIfOpen($postID)
{
    $isOpen = true; //the post is still open

    $conn = connectToDB();
    $query = "SELECT closed FROM Question WHERE questionID ='".$postID."';";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    if($row['closed'] == 1) //the post is closed
    {
        $isOpen = false;
    }

    return $isOpen;
}

//Closes a particular question
function closePost($postID)
{
    $conn = connectToDB();
    $query = "UPDATE Question SET closed = 1 WHERE questionID = '".$postID."';";
    mysqli_query($conn, $query);
}

//Retrieves all of the questionIDs associated with questions posted for a particular language
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

//Retrieves the unique nickname associated with a particular unique userID
function getUserNickname($userID)
{
	$conn = connectToDB();
	$query = "SELECT userNickname FROM User WHERE userID = '".$userID."';";
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_assoc($result);
	$userNickname = $row['userNickname'];
	return $userNickname;
}

//Retrieves all of the answerIDs of the associated answers for a particular question
function getAllAnswers($postID)
{
	$conn = connectToDB();
    $answerIDs = array();
    $query = "SELECT answerID FROM Answer WHERE questionID = '".$postID."' ORDER BY numUpvotes DESC;";
    $result = mysqli_query($conn, $query);
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_assoc($result))
		{
		    $id = intval($row["answerID"]);
            array_push($answerIDs, $id);
		}
	}
	return $answerIDs;
}

//Retrieves detailed information for an answer with the particular answerID
function getAnswer($answerID)
{
	$conn = connectToDB();
    $answerInfo = array();
    $query = "SELECT answerDateTime, answerContent, numUpvotes FROM Answer WHERE answerID = '".$answerID."';";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $answerInfo['datetime'] = $row['answerDateTime'];
	$answerInfo['content'] = $row['answerContent'];
	$answerInfo['numUpvotes'] = $row['numUpvotes'];

	$query2 = "SELECT userID FROM UserAnswer WHERE answerID = '".$answerID."';";
	$result2 = mysqli_query($conn, $query2);
	$row2 = mysqli_fetch_assoc($result2);
	$nickname = getUserNickname($row2['userID']);
	$answerInfo['userNickname'] = $nickname;

    return $answerInfo;
}

//Adds an upvote to a particular answer
function upvoteAnswer($userID, $answerID)
{
	$conn = connectToDB();
	$query = "UPDATE Answer SET numUpvotes = (numUpvotes + 1) WHERE answerID = '".$answerID."';";
	mysqli_query($conn, $query);

	$query2 = "INSERT INTO UserUpvotedAnswer VALUES ('".$userID."', '".$answerID."');";
	mysqli_query($conn, $query2);
}

//Checks whether a particular user has already upvoted a particular answer
function userUpvotedAnswer($userID, $answerID)
{
	$conn = connectToDB();
	$query = "SELECT * FROM UserUpvotedAnswer WHERE userID = '".$userID."' AND answerUpvotedID = '".$answerID."';";
	$result = mysqli_query($conn, $query);
	$alreadyUpvoted = false; //user has not upvoted the answer

	if(mysqli_num_rows($result) > 0) //user has upvoted the answer already
	{
		$alreadyUpvoted = true;
	}

	return $alreadyUpvoted;
}

//Checks whether a particular user has already reported a particular question
function userReportedQuestion($userID, $postID)
{
	$conn = connectToDB();
	$query = "SELECT * FROM UserReportedQuestion WHERE userID = '".$userID."' AND questionReportedID = '".$postID."';";
	$result = mysqli_query($conn, $query);
	$alreadyReported = false; //user has not reported the question

	if(mysqli_num_rows($result) > 0) //user has reported the question already
	{
		$alreadyReported = true;
	}

	return $alreadyReported;
}

//Checks whether a particular user has already reported a particular answer
function userReportedAnswer($userID, $answerID)
{
	$conn = connectToDB();
	$query = "SELECT * FROM UserReportedAnswer WHERE userID = '".$userID."' AND answerReportedID = '".$answerID."';";
	$result = mysqli_query($conn, $query);
	$alreadyReported = false; //user has not reported the answer

	if(mysqli_num_rows($result) > 0) //user has reported the answer already
	{
		$alreadyReported = true;
	}

	return $alreadyReported;	
}

//Reports a particular question for abusive content
function reportQuestion($content, $reporterID, $reportedID, $postID)
{
	$conn = connectToDB();
	$query = "INSERT INTO AbuseReport VALUES (null, '".$content."', 1, 0);";
	mysqli_query($conn, $query);
	$reportID = mysqli_insert_id($conn);

	$queryLink = "INSERT INTO UserReportedQuestion VALUES ('".$reporterID."', '".$postID."');";
	mysqli_query($conn, $queryLink);
	
	$queryLink2 = "INSERT INTO UserAbusiveQuestion VALUES ('".$reportedID."', '".$reportID."', '".$postID."');";
	mysqli_query($conn, $queryLink2);
}

//Reports a particular answer for abusive content
function reportAnswer($content, $reporterID, $reportedID, $answerID)
{
	$conn = connectToDB();
	$query = "INSERT INTO AbuseReport VALUES (null, '".$content."', 0, 0);";
	mysqli_query($conn, $query);
	$reportID = mysqli_insert_id($conn);
	
	$queryLink2 = "INSERT INTO UserAbusiveAnswer VALUES ('".$reportedID."', '".$reportID."', '".$answerID."');";
	mysqli_query($conn, $queryLink2);

	$queryLink = "INSERT INTO UserReportedAnswer VALUES ('".$reporterID."', '".$answerID."');";
	mysqli_query($conn, $queryLink);
}

//Retrieves abuseIDs for all abuse reports that have not already been cleared by a moderator 
function getOpenAbuseReports()
{
	$conn = connectToDB();
	$query = "SELECT abuseID FROM AbuseReport WHERE cleared = 0;";
	$abuseIDs = array();
	$result = mysqli_query($conn, $query);
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_assoc($result))
		{
		    $id = intval($row["abuseID"]);
            array_push($abuseIDs, $id);
		}
	}
	return $abuseIDs;
}

//Retrieves the message submitted with a particular abuse report
function getAbuseReportContent($abuseID)
{
	$conn = connectToDB();
	$query = "SELECT abuseContent FROM AbuseReport WHERE abuseID = '".$abuseID."';";
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_assoc($result);
    $reportContent = $row['abuseContent'];
	return $reportContent;
}

//Retrieves and compiles information about a particular abuse report to display in a table
function getAbuseInfo($abuseID)
{
	$conn = connectToDB();
	$abuseInfo = array();
	$abuseInfo['content'] = getAbuseReportContent($abuseID);
	
	$checkPostTypeQuery = "SELECT question FROM AbuseReport WHERE abuseID = '".$abuseID."';";
	$result = mysqli_query($conn, $checkPostTypeQuery);
	$row = mysqli_fetch_assoc($result);
	$postTypeID = intval($row['question']);
	if($postTypeID == 1) //the post is a question
	{
		$postIDQuery = "SELECT userID, abusivePostID FROM UserAbusiveQuestion WHERE abuseReportID = '".$abuseID."';";
		$result2 = mysqli_query($conn, $postIDQuery);
		$row2 = mysqli_fetch_assoc($result2);
		$posterEmail = getUserEmail(intval($row2['userID']));
		$abuseInfo['email'] = $posterEmail;
		$postContent = getQuestionContent(intval($row2['abusivePostID']));
		$abuseInfo['postContent'] = $postContent;
	}
	else //the post is an answer
	{
		$postIDQuery = "SELECT abusivePostID, userID FROM UserAbusiveAnswer WHERE abuseReportID = '".$abuseID."';";
		$result3 = mysqli_query($conn, $postIDQuery);
		$row3 = mysqli_fetch_assoc($result3);
		$posterEmail = getUserEmail(intval($row3['userID']));
		$abuseInfo['email'] = $posterEmail;
		$postContent = getAnswerContent(intval($row3['abusivePostID']));
		$abuseInfo['postContent'] = $postContent;
	}
	return $abuseInfo;
}

//Retrieves a User's unique email address using their unique userID
function getUserEmail($userID)
{
	$conn = connectToDB();
	$query = "SELECT userEmail FROM User WHERE userID = '".$userID."';";
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_assoc($result);
	$userEmail = $row['userEmail'];
	return $userEmail;
}

//Retrieves the content of a particular question post
function getQuestionContent($questionID)
{
	$conn = connectToDB();
	$query = "SELECT questionContent FROM Question WHERE questionID = '".$questionID."';";
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_assoc($result);
	$questionContent = $row['questionContent'];
	return $questionContent;
}

//Retrieves the content of a particular answer post
function getAnswerContent($answerID)
{
	$conn = connectToDB();
	$query = "SELECT answerContent FROM Answer WHERE answerID = '".$answerID."';";
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_assoc($result);
	$answerContent = $row['answerContent'];
	return $answerContent;
}

//Sets a particular abuse report's ID to cleared
//Used for both clearing abuse reports (with no response) and when deleting an abusive post (responding to report)
function clearAbuse($abuseID)
{
	$conn = connectToDB();
	$query = "UPDATE AbuseReport SET cleared = 1 WHERE abuseID = '".$abuseID."';";
	mysqli_query($conn, $query);
}

//Deletes a post that has been determined by a moderator to be abusive
//Maintains a record of the content of the abusive post in order to keep an abuse history for users
function deletePost($abuseID)
{
    $conn = connectToDB();
	$checkPostTypeQuery = "SELECT question FROM AbuseReport WHERE abuseID = '".$abuseID."';";
	$result = mysqli_query($conn, $checkPostTypeQuery);
	$row = mysqli_fetch_assoc($result);
	$postTypeID = intval($row['question']);
	$posterEmail = "";
	$posterContent = "";
	$deletePostQuery = "";
	
	if($postTypeID == 1) //the post is a question
	{
		$postIDQuery = "SELECT userID, abusivePostID FROM UserAbusiveQuestion WHERE abuseReportID = '".$abuseID."';";
		$result2 = mysqli_query($conn, $postIDQuery);
		$row2 = mysqli_fetch_assoc($result2);
		$abuserID = intval($row2['userID']);
		$posterEmail = getUserEmail($abuserID);
		$abusivePostID = intval($row2['abusivePostID']);
		$postContent = getQuestionContent($abusivePostID);
		$deletePostQuery = "DELETE FROM Question WHERE questionID = '".$abusivePostID."';";
	}
	else //the post is an answer
	{
		$postIDQuery = "SELECT abusivePostID, userID FROM UserAbusiveAnswer WHERE abuseReportID = '".$abuseID."';";
		$result3 = mysqli_query($conn, $postIDQuery);
		$row3 = mysqli_fetch_assoc($result3);
		$abuserID = intval($row3['userID']);
		$posterEmail = getUserEmail($abuserID);
		$abusivePostID = intval($row3['abusivePostID']);
		$postContent = getAnswerContent($abusivePostID);
		$deletePostQuery = "DELETE FROM Answer WHERE answerID = '".$abusivePostID."';";
	}
	
	mysqli_query($conn, $deletePostQuery);
	$saveRecordQuery = "INSERT INTO AbusivePostHistory VALUES (null, '".$postContent."');";
	mysqli_query($conn, $saveRecordQuery);
	
	$recordID = mysqli_insert_id($conn);
	$recordLinkQuery = "INSERT INTO UserAbuseHistory VALUES ('".$posterEmail."', '".$recordID."');";
	mysqli_query($conn, $recordLinkQuery);
}

//Retrieves the emails for all active, non-banned users in the system
function getAllUserEmails()
{
	$conn = connectToDB();
    $emails = array();
    $query = "SELECT userEmail FROM User WHERE active = 1 AND banned = 0;";
    $result = mysqli_query($conn, $query);
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_assoc($result))
		{
		    $email = $row["userEmail"];
            array_push($emails, $email);
		}
	}
	return $emails;
}

//Retrieves the history of abusive posts by a particular user
function getAbuseHistory($email)
{
	$conn = connectToDB();
	$abuseHistoryIDs = array();
	$query = "SELECT abuseHistoryID FROM UserAbuseHistory WHERE userEmail = '".$email."';";
    $result = mysqli_query($conn, $query);
	if (mysqli_num_rows($result) > 0)
	{
		while ($row = mysqli_fetch_assoc($result))
		{
		    $abuseHistoryID = intval($row["abuseHistoryID"]);
            array_push($abuseHistoryIDs, $abuseHistoryID);
		}
	}
	return $abuseHistoryIDs;
}

//Retrieves the abusive post content for a particular abuseHistoryID
function getAbuseHistoryContent($abuseHistoryID)
{
	$conn = connectToDB();
	$content = "";
	$query = "SELECT abusivePostContent FROM AbusivePostHistory WHERE abuseHistoryID = '".$abuseHistoryID."';";
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_assoc($result);
	$content = $row['abusivePostContent'];
	return $content;
}

//Sets a particular User to banned so they can no longer access the system
function banAbusiveUser($userEmail)
{
	$conn = connectToDB();
	$query = "UPDATE User SET banned = 1 WHERE userEmail = '".$userEmail."';";
	mysqli_query($conn, $query);
}
?>

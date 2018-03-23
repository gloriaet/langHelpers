<?php
//Author: Gloria Temple
//10 November 2017
//This program manages all pages for the LangHelpers system
//  This will include both moderator and regular user side functionality
//  For project details check:
//  gtemple1.create.stedwards.edu/cosc4157/

require "utilities.php";
require "langHelpersUtils.php";

printDocHeading("start.css", "LangHelpers");
print "<body>\n<div class='content'>\n";
print "<div class='center'>\n";
print "<h1>LangHelpers</h1>\n";
print "</div>\n"; //end of centered text div
print "</div>\n"; //end of content div
print "<div class='content'>\n";

session_start();

if(!isset($_SESSION['userID']) && !isset($_SESSION['modID']))
{
    if(empty($_POST) || $_POST['returnHome'])
    {
        showMainPage();
    }

    if($_POST['logIn'])
    {
        showLogInForm();
    }

    else if($_POST['submitLogIn'])
    {
        checkCredentials();
    }

    else if($_POST['signUp'])
    {
        showSignUpForm();
    }

    else if($_POST['submitSignUp'])
    {
        showSignUpConfirmation();
    }
}

if(isset($_SESSION['moderator']))
{
	if($_POST['logOut'])
	{
		doLogout();
	}
	
	else if($_POST['viewReports'])
	{
		viewAbuseReports();
	}
	
	else if ($_POST['viewDetailedReport'])
	{
		viewDetailedAbuseReport();
	}
	
	else if($_POST['clearAbuseReport'])
	{
		clearAbuseReport();
	}
	
	else if($_POST['deletePost'])
	{
		deleteAbusivePost();
	}
	
	else if($_POST['chooseUser'])
	{
		displayActiveUsers();
	}
	
	else if($_POST['submitUserChoice'])
	{
		showAbuseHistory();
	}
	
	else if($_POST['banUser'])
	{
		banUser();
	}
	
	else
	{
		displayModeratorHome();
	}
}

if(isset($_SESSION['userID']))
{
    if($_POST['logOut'])
    {
        doLogout();
    }

    else if($_POST['changeLanguage'])
    {
        displayLanguageChoice();
    }

    else if($_POST['createQuestionPost'])
    {
        showQuestionPostForm("");
    }

    else if($_POST['submitQuestionPost'])
    {
        processQuestionPost();
    }

    else if($_POST['viewMyPosts'])
    {
        showUserPosts();
    }

    else if($_POST['viewMyPost'])
    {
        showUserPost();
    }

    else if($_POST['closePost'])
    {
        closeUserPost();
    }

    else if($_POST['viewBoard'])
    {
        viewLanguageBoard();
    }

    else if($_POST['viewPost'])
    {
        showPost();
    }

    else if($_POST['createAnswer'])
    {
        showAnswerPostForm();
    }

    else if($_POST['submitAnswerPost'])
    {
        processAnswerPost();
    }

    else if($_POST['upvoteAnswer'])
    {
        processUpvote();
    }
	
	else if($_POST['reportQuestionAbuse'])
	{
		showQuestionAbuseReportForm();
	}
	
	else if($_POST['submitQuestionAbuseReport'])
	{
		processQuestionAbuseReport();
	}
	
	else if($_POST['reportAnswerAbuse'])
	{
		showAnswerAbuseReportForm();
	}
	
	else if($_POST['submitAnswerAbuseReport'])
	{
		processAnswerAbuseReport();
	}

    else if($_POST['submitLanguage'])
    {
        changeUserLanguage();
        displayUserHome();
    }

    else if(userChoseFirstLanguage($_SESSION['userID']))
    {
        displayUserHome();
    }

    else
    {
        displayLanguageChoice();
    }
}

print "</div>\n";  // end of content div

function showMainPage()
{
    $self = $_SERVER['PHP_SELF'];

    //print "<h2> This will be the main page in the future! It will display neat features/info! </h2>\n";

    print "<h2> Welcome! </h2>\n";

	print "<div> <form method='post' action='$self' >\n";
    print "<h5> <input type='submit' name='logIn' ".
	      " value='Log In' /></h5>\n";
	print "<h5> <input type='submit' name='signUp' value='Sign Up' /></h5>\n";
	print "</form>\n</div>\n";
}

function showLogInForm()
{
    $self = $_SERVER['PHP_SELF'];

	print "<div> <form method='post' action='$self' >\n";
	print "<h3> Email: <input type='text' name='theEmail' value='' /></h3>\n";
	print "<h3> Password: <input type='password' name='thePassword' value='' /></h3>\n";
	print "<h3> <input type='submit' name='submitLogIn' value='Log In' /> </h3>\n";
	print "</form>\n</div>\n";
}

function checkCredentials()
{
    $email = htmlentities($_POST['theEmail']);
	$password = htmlentities($_POST['thePassword']);
	if(checkUser($email, $password))
	{
	    $id = getUserID($email);
	    $_SESSION['userID'] = $id;
	    $_SESSION['userLangID'] = getUserLanguageID($id);
	}
	else if(checkModerator($email, $password))
	{
		$_SESSION['moderator'] = true;
	}
	else
	{
	    print "Invalid credentials. Please try again.\n";
	    showLogInForm();
	}
}

function showSignUpForm()
{
    $self = $_SERVER['PHP_SELF'];

    print "<form method = 'post' action = '$self' >\n";
    print "<h3> Please enter the information below to create your account. </h3>\n";
    print "<h4> Note: This website does not have the capability to secure your account information. Please do not use a nickname or password that you use for any other service.</h4>\n";
    print "<h3> Email: <input type = 'text' name = 'theEmail' value = '' /> </h3>\n";
    print "<h3> Nickname: <input type = 'text' name = 'theNickname' value = '' /> </h3>\n";
    print "<h3> Password: <input type = 'password' name = 'thePassword' value = '' /> </h3>\n";
    print "<h3> First Name: <input type = 'text' name = 'theFirstName' value = '' /> </h3>\n";
    print "<h3> Last Name: <input type = 'text' name = 'theLastName' value = '' /> </h3>\n";
    print "<h3> <input type = 'submit' name = 'submitSignUp' value = 'Sign Up' /> </h3>\n";
}

function showSignUpConfirmation()
{
    $email = htmlentities($_POST['theEmail']);
    $nickname = htmlentities($_POST['theNickname']);
    $password = htmlentities($_POST['thePassword']);
    $firstName = htmlentities($_POST['theFirstName']);
    $lastName = htmlentities($_POST['theLastName']);
    $hash = md5(rand(0, 1000));
    $emailPattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/";

    //all fields must be filled in, so first check for blanks
    if($email == "" || $nickname == "" || $password == "" || $firstName == "" || $lastName == "")
    {
        print "<h2> You must fill in all fields before submitting. </h2>\n";
        showSignUpForm();
    }

    //email must follow standard form xxx@xxx.xxx
    //using regular expressions to check for this
    else if(!preg_match($emailPattern, $email))
    {
        print "<h2> Your email must be of the form xxx@xxx.xxx </h2>\n";
        showSignUpForm();
    }

    //email must be unique, check if email already in database
    else if(checkEmail($email))
    {
        print "<h2> This email is already in use on an account. Please use a different email address. </h2>\n";
        showSignUpForm();
    }

    //nickname must be unique, check if nickname already in database
    else if(checkNickname($nickname))
    {
        print "<h2> This nickname is taken. Please choose another. </h2>\n";
        showSignUpForm();
    }

    //all fields are filled correctly
    //email and nickname are both unique
    //create account for user and send verification email
    else
    {
        createAccount($email, $nickname, $password, $firstName, $lastName, $hash);
        $to = $email;
        $subject = "LangHelpers Sign Up Verification";
        $message = '

        Thank you for signing up with LangHelpers!
        You will be able to log into the system using the credentials you created your account with as soon as you activate your account.

        Activate your account by clicking the link below.
        http://gtemple1.create.stedwards.edu/langHelpers/verifyAccount.php?email='.$email.'&hash='.$hash.'

        ';
        $headers = 'From:noreply@langHelpers.com' . "\r\n";
        mail($to, $subject, $message, $headers);
        print "<p> Thank you for registering an account with LangHelpers!<br><br>\n";
        print "A verification message has been sent to the email you just<br>\n";
        print "registered with. Click the link in the email to activate your <br>\n";
        print "account and start working with LangHelpers!</p><br>\n";
        print "<a href = 'langHelpers.php'>Return Home</a>\n";

    }
}

function doLogout()
{
    $self = $_SERVER['PHP_SELF'];

    session_unset();
    session_destroy();

    print "You have successfully signed out of LangHelpers.<br><br>\n";
    print "Click below to return to the main page.<br><br>\n";
    print "<div> <form method='post' action='$self' >\n";
    print "<h5> <input type='submit' name='returnHome' ".
	      " value='Return Home' /></h5>\n";
	print "</form>\n</div>\n";
}

function displayLanguageChoice()
{
    $self = $_SERVER['PHP_SELF'];
    print "<strong>Please choose a language to work in. You may change your language again at any time.</strong>\n";

    $languages = getAllLanguages();

    print "<div> <form method='post' action='$self' >\n";
    print "<h4> <select name = 'languageChoice' >\n";
    for($i = 0; $i < sizeof($languages); $i++)
    {
        print "<option value = '$i'> $languages[$i] </option>\n";
    }
    print "</select></h4>\n";
    print "<h5> <input type='submit' name='submitLanguage' ".
	      " value='Submit' /></h5>\n";
	print "</form>\n</div>\n";
}

function changeUserLanguage()
{
    $languageID = $_POST['languageChoice'] + 1;
    $userID = $_SESSION['userID'];
    setUserLanguage($userID, $languageID);

    $_SESSION['userLangID'] = getUserLanguageID($userID);
}

function displayModeratorHome()
{
    $self = $_SERVER['PHP_SELF'];

    print "<strong>Welcome Moderator!</strong><br>\n";
    print "<div> <form method='post' action='$self' >\n";
	print "<h5> <input type='submit' name='viewReports' value='View Reports' /></h5>\n";
	print "<h5> <input type='submit' name='chooseUser' value='Abuse History' /></h5>\n";
    print "<h5> <input type='submit' name='logOut' value='Log Out' /></h5>\n";
	print "</form>\n</div>\n";
}

function displayUserHome()
{
    $self = $_SERVER['PHP_SELF'];
    $languageName = getLanguageName($_SESSION['userLangID']);

    //print "Hey you made it to the user home page now!<br><br>\n";
    print "Your account currently uses the language: <strong>".$languageName."</strong><br>\n";
    print "<div> <form method='post' action='$self' >\n";
    print "<h5> <input type='submit' name='changeLanguage' value='Change Language' /></h5>\n";
	print "<h5> <input type='submit' name='viewBoard' ".
	      " value='View Board' /></h5>\n";
    print "<h5> <input type='submit' name='createQuestionPost' ".
	      " value='Create Question' /></h5>\n";
	print "<h5> <input type='submit' name='viewMyPosts' value='View My Posts' /></h5>\n";
    print "<h5> <input type='submit' name='logOut' ".
	      " value='Log Out' /></h5>\n";
	print "</form>\n</div>\n";
}

/*function displayLanguageChange()
{
    print "Here, you can change their posting language.";

    print "<div> <form method='post' action='$self' >\n";
    print "<h5> <input type='submit' name='submitLanguage' ".
	      " value='Submit' /></h5>\n";
	print "</form>\n</div>\n";
}*/

function showQuestionPostForm($content)
{
    $self = $_SERVER['PHP_SELF'];
    $languageName = getLanguageName($_SESSION['userLangID']);

    print "<strong>Please fill in the information below to create your post.</strong><br><br>\n";
    print "<strong>Board:</strong> ".$languageName."<br><br>\n";
    print "<div> <form method='post' action='$self' >\n";
    print "<strong>Post Title: <input type='text' name='theTitle' value='' /></strong><br><br>\n";
	print "<strong>Post Content: <textarea name='theContent' rows='8' cols='100'>$content\n".
		  " </textarea> </h3>\n";
	print "<h3> <input type='submit' name='submitQuestionPost' ".
		  " value='Create Post' /> </strong>\n";
	print "</form>\n</div>\n";
}

function processQuestionPost()
{
    $title = htmlentities($_POST['theTitle'], ENT_QUOTES);
	$content = htmlentities($_POST['theContent'], ENT_QUOTES);
	$userID = $_SESSION['userID'];
	$langID = $_SESSION['userLangID'];
	$languageName = getLanguageName($langID);

	if($title == "" || $content == "")
	{
	    print "Your post must have a title and content before you submit.<br>\n";
	    showQuestionPostForm($content);
	}

	else
	{
	    createQuestion($title, $content, $langID, $userID);
	    print "Your question has been added to the <strong>".$languageName." </strong>board!<br><br>\n";
	    print "Click below to return to your dashboard.<br><br>\n";
	    print "<div> <form method='post' action='$self' >\n";
        print "<h5> <input type='submit' name='returnDash' ".
	        " value='Return to Dashboard' /></h5>\n";
	    print "</form>\n</div>\n";
	}
}

function showUserPosts()
{
    $userID = $_SESSION['userID'];
    $postIDArray = getAllUserPosts($userID);
    if(count($postIDArray) > 0)
    {
        print "<h3>Here are your posts!</h3>\n";
        print "<table>\n";
        print "<tr>\n<th>Title</th>\n";
        print "<th>Language</th>\n";
        print "<th>Datetime Posted</th>\n";
		print "<th>Status</th>\n";
		print "<th>View Post</th>\n</tr>\n";
        for($i = count($postIDArray) - 1; $i >= 0; $i--)
        {
            $postID = $postIDArray[$i];
            $postInfoArray = getPostInfo($postID);
            print "<tr>\n<td>".$postInfoArray['title']."</td>\n";
            print "<td>".$postInfoArray['language']."</td>\n";
            print "<td>".$postInfoArray['datetime']."</td>\n";
			print "<td>".$postInfoArray['closed']."</td>\n";
			print "<td><div> <form method='post' action='$self' >\n";
			print "<h5> <input type='submit' name='viewMyPost' ".
				  " value='View' />\n";
			print "<input type='hidden' name='postID' ".
				  " value=".$postID." /></h5>\n";
			print "</form>\n</div></td>\n</tr>\n";
        }
        print "</table>\n";
    }
    else
    {
        print "<h3> You have not made any posts yet! </h3>\n";
    }
    //print "you made it here yo";
    print "<div> <form method='post' action='$self' >\n";
    print "<h5> <input type='submit' name='returnDash' ".
        " value='Return to Dashboard' /></h5>\n";
	print "</form>\n</div>\n";
}

function showUserPost()
{
	//print "You're trying to view a post!<br/>\n";
	$postID = $_POST['postID'];
	//print "The post you are trying to view is ".$postID."<br/>\n";

	$postOpen = checkIfOpen($postID);
	if($postOpen)
	{
	    print "<div> <form method='post' action='$self' >\n";
	    print "<h5> <input type='submit' name='closePost' ".
		      " value='Close Post' />\n";
	    print "<input type='hidden' name='postID' ".
		      " value=".$postID." /></h5>\n";
	    print "</form>\n</div>\n";
	}
	else
	{
		print "<strong>This post is closed.</strong><br/><br/>\n";
	}
	$post = getQuestion($postID);
	print "<strong>".$post['title']."</strong>\n";
	print "<br/><br/>".$post['content']."<br/><br/>\n";
	print "Posted by ".$post['userNickname']." on: ".$post['datetime']."\n";
	print "<div> <form method='post' action='$self' >\n";
	print "<h5> <input type='submit' name='viewMyPosts' value='Return' /></h5>\n";
	print "</form>\n</div>\n";
	print "-------------------------------------------------------------------------------------------------------------------";

	$answerIDs = getAllAnswers($postID);
	if(count($answerIDs) > 0)
	{
		for($i = 0; $i < count($answerIDs); $i++)
		{
			$answer = getAnswer($answerIDs[$i]);
			//$content = $answer['content'];
			print "<br/><br/>".$answer['content']."\n";
			print "<br/><br/>Posted by ".$answer['userNickname']." on: ".$answer['datetime']."\n";
			print "<br/>Upvotes: ".$answer['numUpvotes']."\n";
			print "<br/><br/>-------------------------------------------------------------------------------------------------------------------";
		}
	}

	else
	{
		print "<br/><br/>There are no answers for this post yet!";
	}
}

function closeUserPost()
{
    $postID = $_POST['postID'];
    print "The post has been closed. You will still be able to view this post from your My Posts page, but it will no longer appear on the language board it's associated with.\n";
    print "Click below to return to your My Posts page.";
    closePost($postID);
    print "<div> <form method='post' action='$self' >\n";
	print "<h5> <input type='submit' name='viewMyPosts' value='Return' /></h5>\n";
	print "</form>\n</div>\n";

}

function viewLanguageBoard()
{
	$langID = $_SESSION['userLangID'];
	$langName = getLanguageName($langID);
    $postIDArray = getAllPosts($langID);
    if(count($postIDArray) > 0)
    {
        print "<h3>Here are the currently open ".$langName." posts!</h3>\n";
        print "<table>\n";
        print "<tr>\n<th>Title</th>\n";
        print "<th>Language</th>\n";
        print "<th>Datetime Posted</th>\n";
		print "<th>View Post</th>\n</tr>\n";
        for($i = count($postIDArray) - 1; $i >= 0; $i--)
        {
            $postID = $postIDArray[$i];
            $postInfoArray = getPostInfo($postID);
            print "<tr>\n<td>".$postInfoArray['title']."</td>\n";
            print "<td>".$postInfoArray['language']."</td>\n";
            print "<td>".$postInfoArray['datetime']."</td>\n";
			print "<td><div> <form method='post' action='$self' >\n";
			print "<h5> <input type='submit' name='viewPost' ".
				  " value='View' />\n";
			print "<input type='hidden' name='postID' ".
				  " value=".$postID." /></h5>\n";
			print "</form>\n</div></td>\n</tr>\n";
        }
        print "</table>\n";
    }
    else
    {
        print "<h3> There are no posts on this board yet! </h3>\n";
    }
    print "<div> <form method='post' action='$self' >\n";
    print "<h5> <input type='submit' name='returnDash' ".
        " value='Return to Dashboard' /></h5>\n";
	print "</form>\n</div>\n";
}

function showPost()
{
	//print "You're trying to view a post!<br/>\n";
	$postID = $_POST['postID'];
	$userID = $_SESSION['userID'];
	//print "The post you are trying to view is ".$postID."<br/>\n";

	$post = getQuestion($postID);
	print "<strong>".$post['title']."</strong>\n";
	print "<br/><br/>".$post['content']."<br/><br/>\n";
	print "Posted by ".$post['userNickname']." on: ".$post['datetime']."\n";
	print "<div> <form method='post' action='$self' >\n";
	print "<h5> <input type='submit' name='viewBoard' value='Return' /></h5>\n";
	print "<h5> <input type='submit' name='createAnswer' value='Answer' /></h5>\n";
	if(!userReportedQuestion($userID, $postID))
	{
		print "<h5> <input type='submit' name='reportQuestionAbuse' value='Report Abuse' /></h5>\n";
	}
	print "<h5> <input type='hidden' name='postID' value='".$postID."' /></h5>\n";
	print "<h5> <input type='hidden' name='questionPosterName' value='".$post['userNickname']."' /></h5>\n";
	print "</form>\n</div>\n";
	print "-------------------------------------------------------------------------------------------------------------------";

	$answerIDs = getAllAnswers($postID);
	if(count($answerIDs) > 0) //answers exist for this question
	{
		for($i = 0; $i < count($answerIDs); $i++) //prints answers out in order of oldest to newest by date
		{
			$answer = getAnswer($answerIDs[$i]);
			//$content = $answer['content'];
			print "<br/><br/>".$answer['content']."\n";
			print "<br/><br/>Posted by ".$answer['userNickname']." on: ".$answer['datetime']."\n";
			print "<br/>Upvotes: ".$answer['numUpvotes']."\n";
			print "<div> <form method='post' action='$self' >\n";
			if(!userUpvotedAnswer($userID, $answerIDs[$i]))
			{
				print "<h5> <input type='submit' name='upvoteAnswer' value='Upvote' /></h5?\n";
			}
			if(!userReportedAnswer($userID, $answerIDs[$i]))
			{
				print "<h5> <input type='submit' name='reportAnswerAbuse' value='Report Abuse' /></h5?\n";
			}
			print "<h5> <input type='hidden' name='answerID' value='".$answerIDs[$i]."' /></h5>\n";
			print "<h5> <input type='hidden' name='postID' value='".$postID."' /></h5>\n";
			print "<h5> <input type='hidden' name='answerPosterName' value='".$answer['userNickname']."' /></h5>\n";
			print "</form>\n</div>\n";
			print "-------------------------------------------------------------------------------------------------------------------";
		}
	}

	else
	{
		print "<br/><br/>There are no answers for this post yet!";
	}
}

function showAnswerPostForm()
{
	$self = $_SERVER['PHP_SELF'];
	$languageName = getLanguageName($_SESSION['userLangID']);
	$postID = $_POST['postID'];
	$post = getQuestion($postID);

  print "<strong>Please fill in the information below to create your answer.</strong><br><br>\n";
  print "<strong>Board:</strong> ".$languageName."<br><br>\n";
	print "<strong>Question:</strong> ".$post['content']."<br/><br/>\n";
  print "<div> <form method='post' action='$self' >\n";
  print "<strong>Answer: <textarea name='theContent' rows='8' cols='100'>\n";
  print " </textarea> </h3>\n";
	print "<h3> <input type='hidden' name='postID' value='".$postID."' /></h3>\n";
	print "<h3> <input type='submit' name='submitAnswerPost' ";
  print " value='Submit' /> </strong>\n";
	print "</form>\n</div>\n";
}

function processAnswerPost()
{
	$content = htmlentities($_POST['theContent'], ENT_QUOTES);
	$userID = $_SESSION['userID'];
	$postID = $_POST['postID'];
	$post = getQuestion($postID);

	if($content == "")
	{
	    print "Your answer must have content before you submit.<br>\n";
      print "<div> <form method='post' action='$self' >\n";
      print "<h5> <input type='submit' name='createAnswer' ";
      print " value='Return' /></h5>\n";
      print "<h5> <input type='hidden' name='postID' value='".$postID."' /></h5>\n";
	    print "</form>\n</div>\n";
	}

	else
	{
	    createAnswer($content, $userID, $postID);
	    print "Your answer has been added to the question titled <strong>".$post['title']." </strong>!<br><br>\n";
	    //print "Click below to return to the board.<br><br>\n";
	    print "<div> <form method='post' action='$self' >\n";
        print "<h5> <input type='submit' name='viewPost' ".
	        " value='Return' /></h5>\n";
      print "<h5> <input type='hidden' name='postID' value='".$postID."' /></h5>\n";
	    print "</form>\n</div>\n";
	}
}

function processUpvote()
{
  $self = $_SERVER['PHP_SELF'];
  $userID = $_SESSION['userID'];
  $postID = $_POST['postID'];
  $answerID = $_POST['answerID'];
  upvoteAnswer($userID, $answerID);

  print "Answer succesfully upvoted.<br/><br/>";
  print "<div> <form method='post' action='$self' >\n";
  print "<h5> <input type='submit' name='viewPost' value='Return' /></h5>\n";
  print "<h5> <input type='hidden' name='postID' value='".$postID."' /></h5>\n";
  print "</form>\n</div>\n";
}

function showQuestionAbuseReportForm()
{
	$self = $_SERVER[PHP_SELF];
	$postID = $_POST['postID'];
	$post = getQuestion($postID);
	$posterName = $_POST['questionPosterName'];
	$posterID = getUserIDByNickname($posterName);
	
	print "<strong>Please fill in the information below to report this post for abuse.</strong><br><br>\n";
	print "<strong>Question:</strong> ".$post['content']."<br/><br/>\n";
	print "<div> <form method='post' action='$self' >\n";
	print "<strong>Report: <textarea name='theContent' rows='8' cols='100'>\n";
	print " </textarea> </h3>\n";
	print "<h3> <input type='hidden' name='postID' value='".$postID."' /></h3>\n";
	print "<h3> <input type='hidden' name='posterID' value='".$posterID."' /></h3>\n";
	print "<h3> <input type='hidden' name='questionPosterName' value='".$posterName."' /></h3>\n";
	print "<h3> <input type='submit' name='submitQuestionAbuseReport' ";
	print " value='Submit' /> </strong>\n";
	print "</form>\n</div>\n";
}

function processQuestionAbuseReport()
{
	$content = htmlentities($_POST['theContent'], ENT_QUOTES);
	$userID = $_SESSION['userID'];
	$postID = $_POST['postID'];
	$posterName = $_POST['questionPosterName'];
	$post = getQuestion($postID);
	$posterID = $_POST['posterID'];

	if($content == "")
	{
	    print "Your report must have content before you submit.<br>\n";
		print "<div> <form method='post' action='$self' >\n";
		print "<h5> <input type='submit' name='reportQuestionAbuse' ";
		print " value='Return' /></h5>\n";
		print "<h5> <input type='hidden' name='postID' value='".$postID."' /></h5>\n";
		print "<h5> <input type='hidden' name='questionPosterName' value='".$posterName."' /></h5>\n";
	    print "</form>\n</div>\n";
	}

	else
	{
	    reportQuestion($content, $userID, $posterID, $postID);
	    print "Your abuse report has been received! <br><br>\n";
	    //print "Click below to return to the board.<br><br>\n";
	    print "<div> <form method='post' action='$self' >\n";
        print "<h5> <input type='submit' name='viewPost' ".
	        " value='Return' /></h5>\n";
		print "<h5> <input type='hidden' name='postID' value='".$postID."' /></h5>\n";
	    print "</form>\n</div>\n";
	}
}

function showAnswerAbuseReportForm()
{
	$self = $_SERVER[PHP_SELF];
	$postID = $_POST['postID'];
	$answerID = $_POST['answerID'];
	$answer = getAnswer($answerID);
	$posterName = $_POST['answerPosterName'];
	$posterID = getUserIDByNickname($posterName);
	
	print "<strong>Please fill in the information below to report this post for abuse.</strong><br><br>\n";
	print "<strong>Answer:</strong> ".$answer['content']."<br/><br/>\n";
	print "<div> <form method='post' action='$self' >\n";
	print "<strong>Report: <textarea name='theContent' rows='8' cols='100'>\n";
	print " </textarea> </h3>\n";
	print "<h3> <input type='hidden' name='postID' value='".$postID."' /></h3>\n";
	print "<h3> <input type='hidden' name='answerID' value='".$answerID."' /></h3>\n";
	print "<h3> <input type='hidden' name='posterID' value='".$posterID."' /></h3>\n";
	print "<h3> <input type='hidden' name='answerPosterName' value='".$posterName."' /></h3>\n";
	print "<h3> <input type='submit' name='submitAnswerAbuseReport' ";
	print " value='Submit' /> </strong>\n";
	print "</form>\n</div>\n";
}

function processAnswerAbuseReport()
{
	$content = htmlentities($_POST['theContent'], ENT_QUOTES);
	$userID = $_SESSION['userID'];
	$postID = $_POST['postID'];
	$answerID = $_POST['answerID'];
	$posterName = $_POST['answerPosterName'];
	$post = getAnswer($answerID);
	$posterID = $_POST['posterID'];

	if($content == "")
	{
		print "Your report must have content before you submit.<br>\n";
		print "<div> <form method='post' action='$self' >\n";
		print "<h5> <input type='submit' name='reportAnswerAbuse' ";
		print " value='Return' /></h5>\n";
		print "<h5> <input type='hidden' name='postID' value='".$postID."' /></h5>\n";
		print "<h5> <input type='hidden' name='answerID' value='".$answerID."' /></h5>\n";
		print "<h5> <input type='hidden' name='answerPosterName' value='".$posterName."' /></h5>\n";
	    print "</form>\n</div>\n";
	}

	else
	{
	    reportAnswer($content, $userID, $posterID, $answerID);
	    print "Your abuse report has been received! <br><br>\n";
	    //print "Click below to return to the board.<br><br>\n";
	    print "<div> <form method='post' action='$self' >\n";
        print "<h5> <input type='submit' name='viewPost' value='Return' /></h5>\n";
		print "<h5> <input type='hidden' name='postID' value='".$postID."' /></h5>\n";
	    print "</form>\n</div>\n";
	}
}

function viewAbuseReports()
{
    $abuseIDArray = getOpenAbuseReports();
    if(count($abuseIDArray) > 0)
    {
        print "<h3>Here are the currently open abuse reports.</h3>\n";
        print "<table>\n";
        print "<tr>\n<th>Report Content</th>\n";
		print "<th>View Details</th>\n</tr>\n";
        for($i = count($abuseIDArray) - 1; $i >= 0; $i--)
        {
            $abuseID = $abuseIDArray[$i];
            $abuseContent = getAbuseReportContent($abuseID);
            print "<tr>\n<td>".$abuseContent."</td>\n";
			print "<td><div> <form method='post' action='$self' >\n";
			print "<h5> <input type='submit' name='viewDetailedReport' value='View' />\n";
			print "<input type='hidden' name='abuseID' value=".$abuseID." /></h5>\n";
			print "</form>\n</div></td>\n</tr>\n";
        }
        print "</table>\n";
    }
    else
    {
        print "<h3> There are no active abuse reports! </h3>\n";
    }
	print "<div> <form method='post' action='$self' >\n";
	print "<h5> <input type='submit' name='modHome' value='Return' /></h5>\n";
	print "</form>\n</div>\n";
}

function viewDetailedAbuseReport()
{
	$abuseID = $_POST['abuseID'];
	$abuseInfo = getAbuseInfo($abuseID);
	$abuserEmail = $abuseInfo['email'];
	$abuseReportContent = $abuseInfo['content'];
	$abusivePostContent = $abuseInfo['postContent'];
	
	print "<strong>Abuser: </strong>".$abuserEmail."<br/><br/>\n";
	print "<strong>Report: </strong>".$abuseReportContent."<br/><br/>\n";
	print "<strong>Original Post Content: </strong>".$abusivePostContent."<br/><br/>\n";
	print "<div> <form method='post' action='$self' >\n";
	print "<h5> <input type='hidden' name='abuseID' value='".$abuseID."' /></h5>\n";
	print "<h5> <input type='submit' name='clearAbuseReport' value='Clear Report' /></h5>\n";
	print "<h5> <input type='submit' name='deletePost' value='Delete Post' /></h5>\n";
	print "<h5> <input type='submit' name='viewReports' value='Return' /></h5>\n";
	print "</form>\n</div>\n";
}

function clearAbuseReport()
{
	$abuseID = $_POST['abuseID'];
	clearAbuse($abuseID);
	print "The abuse report has been cleared, no further action needed.<br/><br/>\n";
	print "<div> <form method='post' action='$self' >\n";
	print "<h5> <input type='submit' name='viewReports' value='Return' /></h5>\n";
	print "</form>\n</div>\n";
}

function deleteAbusivePost()
{
	$abuseID = $_POST['abuseID'];
	$abuseInfo = getAbuseInfo($abuseID);
	$abuserEmail = $abuseInfo['email'];
	clearAbuse($abuseID);
	deletePost($abuseID);
	print "The post by <strong>".$abuserEmail."</strong> has been deleted.<br/><br/>\n";
	print "<div> <form method='post' action='$self' >\n";
	print "<h5> <input type='submit' name='viewReports' value='Return' /></h5>\n";
	print "</form>\n</div>\n";
}

function displayActiveUsers()
{
	$self = $_SERVER['PHP_SELF'];
    print "<strong>Please choose a user to view their Abuse History.</strong>\n";

    $userEmails = getAllUserEmails();

    print "<div> <form method='post' action='$self' >\n";
    print "<h4> <select name = 'userChoice' >\n";
    for($i = 0; $i < sizeof($userEmails); $i++)
    {
        print "<option value = '$i'> $userEmails[$i] </option>\n";
    }
    print "</select></h4>\n";
    print "<h5> <input type='submit' name='submitUserChoice' ".
	      " value='Submit' /></h5>\n";
	print "</form>\n</div>\n";
}

function showAbuseHistory()
{
    $selectedUser = $_POST['userChoice'];
	$userEmails = getAllUserEmails();
	$email = $userEmails[$selectedUser];
	$abuseHistoryIDs = getAbuseHistory($email);
	print "Abuse History of <strong>".$email."</strong><br/><br/>\n";
	
	if(count($abuseHistoryIDs) > 0)
	{
		for($i = 0; $i < count($abuseHistoryIDs); $i++)
		{
			$abuseHistoryID = $abuseHistoryIDs[$i];
			$abuseID = $abuseIDArray[$i];
			$abusivePostContent = getAbuseHistoryContent($abuseHistoryID);
			print "-------------------------------------------------------------------------------------------------------------------<br/>\n";
			print "<strong>Post:</strong> ".$abusivePostContent."<br/><br/>\n";
		}
	}
	else
	{
		print "-------------------------------------------------------------------------------------------------------------------<br/>\n";
		print "This user has no history of abusive posts!<br/><br/>\n";
	}
	
	print "<div> <form method='post' action='$self' >\n";
	print "<h5> <input type='hidden' name='userEmail' value='".$email."' /></h5>\n";
	print "<h5> <input type='submit' name='banUser' value='Ban User' /></h5>\n";
	print "<h5> <input type='submit' name='modHome' value='Dashboard' /></h5>\n";
	print "</form>\n</div>\n";
}

function banUser()
{
	$userEmail = $_POST['userEmail'];
	banAbusiveUser($userEmail);
	
	print "You successfully banned <strong>".$userEmail."</strong>!<br/><br/>\n";
	print "<div> <form method='post' action='$self' >\n";
	print "<h5> <input type='submit' name='modHome' value='Dashboard' /></h5>\n";
	print "</form>\n</div>\n";
}

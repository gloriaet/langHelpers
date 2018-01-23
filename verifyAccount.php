<?php
//Author: Gloria Temple
//3 November 2017
//Processes get requests from links generated in account activation emails
//Activates user accounts

require "utilities.php";
require "langHelpersUtils.php";

printDocHeading("start.css", "LangHelpers Sign Up");
print "<body>\n<div class='content'>\n";
print "<div class='center'>\n";
print "<h1>Sign Up</h1>\n";
print "</div>\n"; //end of centered text div
print "</div>\n"; //end of content div
print "<div class='content'>\n";

if(empty($_GET))
{
    print "<h2>You may not access this page in this way. Please use the verification link sent to your email. </h2>\n";
    print "<a href = 'langHelpers.php'>Return Home</a>\n";
}

else
{
    activateAccount();
}

print "</div>\n";  // end of content div

function activateAccount()
{
    $email = htmlentities($_GET['email']);
    $hash = htmlentities($_GET['hash']);
    $match = checkUnactivatedAccount($email, $hash);
    
    if($match > 0)
    {
        updateAccountActivation($email, $hash);
        print "<p>Thank you for verifying your email! Your<br>\n";
        print "account is now activated.<br><br>\n";
        print "Check out the features of your new account<br>\n";
        print "by logging in now!</p>\n";
        print "<a href = 'langHelpers.php'>Return Home</a>\n";
    }
    
    else
    {
        print "<p>Either this URL is invalid, or your account has<br>\n";
        print "already been activated. Please double check the URL <br>\n";
        print "in your verification email or proceed to log in.</p>\n";
        print "<a href = 'langHelpers.php'>Return Home</a>\n";
    }
}

?>
<?php

/**
* This is the page page where the user, coming from the start.php page, can review and confirm the data of their emergency note
*/

// Include the session initializer
include_once 'PHP/internal_libraries/engine/starter.php';

// Check if there is a POST request first
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Verify the secure form token in order to avoid any cross-domain POST request
  // If it's not verified, redirect to the index.php page
  if($_SESSION['csrf_token'] !== $_POST['csrf_token']){
    session_unset();
    session_destroy();
    header("Location: /index.php");
    exit();
  }

  // Set the page token, it will be used in the next page as a verifier
  $_SESSION["page_token"] = "review_page";

  // Set the variables according to the input values (without HTML or PHP tags)
  $subject = strip_tags($_POST["subject"]);
  $note = strip_tags($_POST["note"]);
  $primary_email = strip_tags($_POST["primary_email"]);
  $secondary_email = strip_tags($_POST["secondary_email"]);
  $inactivity_time = strip_tags($_POST["inactivity_time"]);
  $minimum_expiration_date = date('Y-m-d', strtotime(" + {$inactivity_time} days"));

  // If the expiration date hasn't been passed via POST, then set it to "None"
  if (!isset($_POST["expiration_date"])) {
    $expiration_date = "None";
  }

  // If the expiration has been passed via POST and is higher or equal to the minimum expiration date,
  // then set its relative value
  elseif ($_POST["expiration_date"] >= $minimum_expiration_date) {
    $expiration_date = date("Y-m-d", strtotime($_POST["expiration_date"]));
  };

  // For security reasons, truncate the value of the variables in case they are longer than supposed
  $subject = substr($subject, 0, 100);
  $note = substr($note, 0, 1000);
  $primary_email = substr($primary_email, 0, 254);
  $secondary_email = substr($secondary_email, 0, 254);

  // Remove all the invalid characters from the email addresses
  $primary_email = filter_var($primary_email, FILTER_SANITIZE_EMAIL);
  $secondary_email = filter_var($secondary_email, FILTER_SANITIZE_EMAIL);

  // Unset all the session variables, destroy the session and redirect to the start.php page
  // in case the primary and secondary email addresses are the same
  if ($primary_email == $secondary_email) {
    session_unset();
    session_destroy();
    header("Location: /start.php");
    exit();
  }

  // Unset all the session variables, destroy the session and redirect to the start.php page
  // in case one of the mandatory variables is empty
  if (empty($subject) or empty($note) or empty($primary_email) or empty($expiration_date)) {
    session_unset();
    session_destroy();
    header("Location: /start.php");
    exit();
  }

  // Unset all the session variables, destroy the session and redirect to the start.php page
  // in case the inactivity time varialble doesn't match the presets values
  if (!($inactivity_time >= 1 && $inactivity_time <= 30)) {
    session_unset();
    session_destroy();
    header("Location: /start.php");
    exit();
  }

  // Set the session variables that will be used in the next step (done.php page)
  else {
    $_SESSION["subject"] = $subject;
    $_SESSION["note"] = $note;
    $_SESSION["primary_email"] = $primary_email;
    $_SESSION["inactivity_time"] = $inactivity_time;
    $_SESSION["expiration_date"] = $expiration_date;

    // Set the secondary email value to "None" in case the input field was empty
    if (empty($secondary_email)) {
      $_SESSION["secondary_email"] = "None";
    }
    else {
      $_SESSION["secondary_email"] = $secondary_email;
    }
  }

}

// If there is no POST request then remove all the session variables,
// destroy the session and redirect to the start.php page
else {
  session_unset();
  session_destroy();
  header("Location: /start.php");
  exit();
}

// Include the page language switch in order to provide translated content
include_once 'PHP/internal_libraries/engine/page_language_switch.php';

?>
<!doctype html>
<html lang="en">
<head>
  <?php include 'PHP/internal_libraries/page_templates/head.php';?>
</head>
<body>
  <?php include 'PHP/internal_libraries/page_templates/header.php';?>
  <main class="container">
    <div class="row align-items-center min-content-height">
      <div class="col">
        <div class="pb-3 pt-3 pt-md-5 mx-auto text-center">
          <h1 class="display-3"><?=$translation["review_page_title"];?></h1>
        </div>
        <div class="pb-3">
          <div class="row">
            <div class="col-12 col-md-8 offset-md-2 mt-3 text-break">
              <ul>
                <li><p><?=$translation["review_page_information_1"];?></p></li>
                <li><p><?=$translation["review_page_information_2"];?></p></li>
                <li>
                  <?php if ($_SESSION["secondary_email"] !== "None") { ?>
                    <p><?=$translation["review_page_information_3_a"];?></p>
                  <?php } else { ?>
                    <p><?=$translation["review_page_information_3_b"];?></p>
                  <?php } ?>
                </li>
                <li>
                  <?php if ($_SESSION["inactivity_time"] == 1) { ?>
                    <p><?=$translation["review_page_information_4_a"];?></p>
                  <?php } else { ?>
                    <p><?=$translation["review_page_information_4_b"];?></p>
                  <?php } ?>
                </li>
                <?php if ($expiration_date == "None") { ?>
                  <li><p><?=$translation["review_page_information_5_a"];?></p></li>
                <?php } else { ?>
                  <li><p><?=$translation["review_page_information_5_b"];?></p></li>
                <?php } ?>
                <li><p><?=$translation["review_page_information_6"];?></p></li>
                <li><p><?=$translation["review_page_information_7"];?></p></li>
              </ul>
            </div>
          </div>
          <div class="row pt-5 text-center">
            <div class="col">
              <form action="done.php" method="POST">
                <button class="btn btn-primary btn-lg" type="submit"><?=$translation["button_confirm"];?></button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include 'PHP/internal_libraries/page_templates/footer.php';?>
  </main>
</body>
</html>
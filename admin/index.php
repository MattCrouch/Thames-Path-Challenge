<?php
include("../credentials.php");

//Set up connection to database
$conn = new mysqli(SERVER_NAME, SERVER_USERNAME, SERVER_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    exit;
}

$values = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Update
    $updates = "";

    //RunKeeper
    if(isset($_POST['runkeeper'])) {
        if(isset($_POST['runkeeper']['live']) && $_POST['runkeeper']['live'] !== "") {
            $updates .= "UPDATE tpc SET value = " . (int)$_POST['runkeeper']['live'] . " WHERE name = 'live';";
        }
        if(isset($_POST['runkeeper']['clear'])) {
            $updates .= "DELETE FROM tpc_route;";
        }
    }

    //Twitter
    if(isset($_POST['twitter'])) {
        if(isset($_POST['twitter']['clear'])) {
            $updates .= "DELETE FROM tpc_social WHERE source = 'twitter'; UPDATE tpc SET value = 0 WHERE name = 'last_twitter_check';";
        }
    }

    //Instagram
    if(isset($_POST['instagram'])) {
        if(isset($_POST['instagram']['clear'])) {
            $updates .= "DELETE FROM tpc_social WHERE source = 'instagram'; UPDATE tpc SET value = 0 WHERE name = 'last_instagram_check';";
        }
    }

    //Last.fm
    if(isset($_POST['lastfm'])) {
        if(isset($_POST['lastfm']['clear'])) {
            $updates .= "DELETE FROM tpc_last_fm; UPDATE tpc SET value = 0 WHERE name = 'last_last_fm_check';";
        }
    }

    $conn->multi_query($updates);
    
    header('Location: index.php'); //Makes sure we don't land back on a POST page
}

//Get possible RunKeeper activities
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.runkeeper.com/fitnessActivities"); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/vnd.com.runkeeper.LiveFitnessActivityFeed+json',
    'Authorization: Bearer ' . RUNKEEPER_ACCESS_TOKEN
));
$runKeeper = curl_exec($ch); 
curl_close($ch);

$runKeeper = json_decode($runKeeper);

$runKeeperActivities = array();
foreach($runKeeper->items as $activity) {
    $uri = explode('/', $activity->uri);
    $activityId = end($uri);
    $runKeeperActivities[$activityId] = $activity->start_time;
}

//Get current values
$sql = "SELECT * FROM tpc;";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $values[$row['name']] = array(
            'value' => $row['value'],
            'last_updated' => date("d/m/Y H:i:s", strtotime($row['last_updated']))
        );
    }
}

$conn->close(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width">
  <meta charset="utf-8">
  <title>TPC Admin</title>
  <style type="text/css">
    html {
        line-height: 1.5em;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    }

    h1, h2 {
        text-align: center;
    }

    label {
        display: block;
    }

    input[type=submit] {
        display: block;
        margin-top: 20px;
        width: 100%;
        height: 5em;
    }
  </style>
</head>
<body>
    <h1>TPC Admin</h1>
    <h2><?=date("d/m/Y H:i:s")?></h2>
    <form method="POST">
        <fieldset>
            <legend>RunKeeper</legend>
            
            <div class="currently">
                <?php if(isset($values['live'])) {?>
                    Currently: <?=$values['live']['value'] !== "0" ? (int)$values['live']['value'] : "Not Live"?><br/>
                    Last Updated: <?=$values['live']['last_updated']?>
                <?php } ?>
            </div>
            
            <label>
                <select name="runkeeper[live]">
                    <option value="">No Change</option>
                    <option value="0">TURN OFF LIVE</option>
                    <?php foreach($runKeeperActivities as $activityId => $activity) {?>
                        <option value="<?=(int)$activityId?>"><?=(int)$activityId?> - <?=$activity?></option>
                    <?php } ?>
                </select>
            </label>

            <label>
                <input type="checkbox" name="runkeeper[clear]"/>
                Clear?
            </label>
        </fieldset>

        <fieldset>
            <legend>Twitter</legend>
            
            <div class="currently">
                <?php if(isset($values['last_twitter_check'])) {?>
                    Currently: <?=(int)$values['last_twitter_check']['value']?><br/>
                    Last Updated: <?=$values['last_twitter_check']['last_updated']?>
                <?php } ?>
            </div>

            <label>
                <input type="checkbox" name="twitter[clear]"/>
                Clear?
            </label>
        </fieldset>

        <fieldset>
            <legend>Instagram</legend>
            
            <div class="currently">
                <?php if(isset($values['last_instagram_check'])) {?>
                    Currently: <?=(int)$values['last_instagram_check']['value']?><br/>
                    Last Updated: <?=$values['last_instagram_check']['last_updated']?>
                <?php } ?>
            </div>

            <label>
                <input type="checkbox" name="instagram[clear]"/>
                Clear?
            </label>
        </fieldset>

        <fieldset>
            <legend>Last.fm</legend>

            <div class="currently">
                <?php if(isset($values['last_last_fm_check'])) {?>
                    Currently: <?=(int)$values['last_last_fm_check']['value']?><br/>
                    Last Updated: <?=$values['last_last_fm_check']['last_updated']?>
                <?php } ?>
            </div>
            
            <label>
                <input type="checkbox" name="lastfm[clear]"/>
                Clear?
            </label>
        </fieldset>

        <input type="submit" value="Update"/>
    </form>
</body>
</html>
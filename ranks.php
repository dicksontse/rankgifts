<!DOCTYPE html>
<html>
    <head>
        <meta charset='utf-8'>
        <title>RankGifts | Top 10 Gifts</title>
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/main.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <?php
        if (is_file('settings.php'))
        {
          include 'settings.php';
        }

        $link = mysql_connect(DB_SERVER, DB_USER, DB_PW) or die('Could not connect: ' . mysql_error());
        mysql_select_db('rankgifts') or die('Could not select database');

        $query = 'SELECT * FROM products ORDER BY points DESC LIMIT 10';
        $result = mysql_query($query) or die('Query failed: ' . mysql_error());

        $gifts = array();
        for ($i = 0; $i < 10; $i++)
        {
          $gifts[] = mysql_fetch_array($result);
        }

        mysql_free_result($result);

        require 'lib/AmazonECS.class.php';

        try
        {
          $amazonEcs = new AmazonECS(AWS_API_KEY, AWS_API_SECRET_KEY, 'COM', AWS_ASSOCIATE_TAG);
          $amazonEcs->setReturnType(AmazonECS::RETURN_TYPE_ARRAY);
        }
        catch(Exception $e)
        {
          echo $e->getMessage();
        }

        if ($_POST['add-ASIN'])
        {
          $response = $amazonEcs->lookup($_POST['add-ASIN']);
          if (isset($response['Items']['Request']['Errors']))
          {
            $invalid = true;
          }
          else {
            $query = "SELECT * FROM products WHERE ASIN = '" . $_POST['add-ASIN'] . "'";
            $result = mysql_query($query) or die('Query failed: ' . mysql_error());
            $checkGift = mysql_fetch_array($result);
            if ($checkGift)
            {
              // ASIN already in db
            }
            else
            {
              $query = "INSERT INTO products (ASIN) VALUES ('" . $_POST['add-ASIN'] . "')";
              $result = mysql_query($query) or die('Query failed: ' . mysql_error());
              $added = true;
            }
          }
        }

        mysql_close($link);

        ?>
    </head>
    <body>
        <div class="navbar navbar-fixed-top navbar-inverse">
            <div class="navbar-inner">
                <div class="container">
                    <a class="brand" href="index.php">RankGifts</a>
                    <ul class="nav">
                        <li class="active"><a href="ranks.php">Top 10</a></li>
                    </ul>
                    <form class="navbar-form pull-right" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                      <input type="text" class="span2" name="add-ASIN" maxlength="10" placeholder="Enter ASIN">
                      <button type="submit" class="btn">Add</button>
                    </form>
                </div>
            </div>
        </div>
        <div id="wrap">
            <div id="main" class="container">
                <?php
                if ($checkGift)
                {
                  echo '<div class="alert alert-info"><button data-dismiss="alert" class="close" type="button">×</button><strong>Already exists:</strong> Please try adding another gift!</div>';
                }
                else if ($invalid)
                {
                  echo '<div class="alert alert-error"><button data-dismiss="alert" class="close" type="button">×</button><strong>Invalid ASIN:</strong> Please try again. :(</div>';
                }
                else if ($added)
                {
                  echo '<div class="alert alert-success"><button data-dismiss="alert" class="close" type="button">×</button><strong>Success!</strong> Thank you for adding a gift! :)</div>';
                }
                ?>
                <h2>Top 10 Gifts</h2>
                  <?php
                  for ($i = 0; $i < count($gifts); $i++)
                  {
                    echo '<div class="row">';
                    echo '<div class="lead">#' . ($i + 1) . "</div>";
                    $response = $amazonEcs->responseGroup('Small,Images')->lookup($gifts[$i]['ASIN']);

                    if (isset($response['Items']['Item']) ) {
                      $item1 = $response['Items']['Item'];

                      if (isset($item1['ASIN'])) {
                        if (isset($item1['DetailPageURL'])) {
                          if (isset($item1['ItemAttributes']['Title'])) {
                            echo "<div class='lead'><a href='" . $item1['DetailPageURL'] . "' target='_blank'>" . $item1['ItemAttributes']['Title'] . "</a></div>";
                          }

                          if (isset($item1['SmallImage']['URL'] )) {
                            echo "<a href='" . $item1['DetailPageURL'] . "' target='_blank'><img src='" . $item1['SmallImage']['URL'] . "'></a>";
                          }
                        }
                      }
                    }

                    echo '</div>';
                  }
                  ?>
            </div>
            <div id="push"></div>
        </div>
        <div id="footer">
            <div class="container">
                <div class="row">
                    <div class="span3">
                        <p class="muted"><a href="http://dicksontse.com" target="_blank">dicksontse.com</a></p>
                    </div>
                    <div id="credit" class="span9">
                        <p>&copy; 2012 Dickson Tse</p>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

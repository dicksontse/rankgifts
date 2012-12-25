<!DOCTYPE html>
<html>
    <head>
        <meta charset='utf-8'>
        <title>RankGifts</title>
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/main.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script>
          $(document).ready(function () {
            $("[rel=popover]").popover({'placement':'bottom', 'trigger':'focus'});
          });
        </script>
        <?php
        if (is_file('settings.php'))
        {
          include 'settings.php';
        }

        function truncate_title($title, $limit) {
          if (strlen($title) > $limit)
          {
            $title = substr($title, 0, $limit) . '...';
          }
          return $title;
        }

        $link = mysql_connect(DB_SERVER, DB_USER, DB_PW) or die('Could not connect: ' . mysql_error());
        mysql_select_db(DB_NAME) or die('Could not select database');

        if ($_POST['update-ASIN'])
        {
          $query = "UPDATE products SET points = points + 1 WHERE ASIN = '" . $_POST['update-ASIN'] . "'";
          $result = mysql_query($query) or die('Query failed: ' . mysql_error());
        }

        $query = 'SELECT * FROM products ORDER BY RAND() LIMIT 2';
        $result = mysql_query($query) or die('Query failed: ' . mysql_error());

        $gift1 = mysql_fetch_array($result);
        $gift2 = mysql_fetch_array($result);

        mysql_free_result($result);

        require 'lib/AmazonECS.class.php';

        try
        {
          $amazonEcs = new AmazonECS(AWS_API_KEY, AWS_API_SECRET_KEY, 'COM', AWS_ASSOCIATE_TAG);
          $amazonEcs->setReturnType(AmazonECS::RETURN_TYPE_ARRAY);

          if ($gift1['timestamp'])
          {
            $timeDiff = time() - $gift1['timestamp'];
            if ($timeDiff < 3600) // Refresh product data if older than 1 hour
            {
              $gift1Refreshed = true;
            }
            else
            {
              $gift1Refreshed = false;
            }
          }
          else
          {
            $gift1Refreshed = false;
          }

          if ($gift1Refreshed)
          {
            $item1Title = $gift1['Title'];
            $item1PageURL = $gift1['PageURL'];
            $item1ImageURL = $gift1['ImageURL'];
          }
          else
          {
            $response = $amazonEcs->responseGroup('Small,Images')->lookup($gift1['ASIN']);

            if (isset($response['Items']['Item']) ) {
              $item1 = $response['Items']['Item'];

              if (isset($item1['ASIN'])) {
                if (isset($item1['DetailPageURL'])) {
                  if (isset($item1['ItemAttributes']['Title'])) {
                    $item1PageURL = $item1['DetailPageURL'];
                    $item1Title = $item1['ItemAttributes']['Title'];
                  }

                  if (isset($item1['LargeImage']['URL'] )) {
                    $item1ImageURL = $item1['LargeImage']['URL'];
                  }
                }
              }

              $query = "UPDATE products SET Title = '" . str_replace("'", "\'", $item1Title) . "', PageURL = '" . str_replace("'", "\'", $item1PageURL) . "', ImageURL = '" . str_replace("'", "\'", $item1ImageURL) . "', timestamp = '" . time() . "' WHERE ASIN = '" . $gift1['ASIN'] . "'";
              $result = mysql_query($query) or die('Query failed: ' . mysql_error());
            }
          }

          if ($gift2['timestamp'])
          {
            $timeDiff = time() - $gift2['timestamp'];
            if ($timeDiff < 3600) // Refresh product data if older than 1 hour
            {
              $gift2Refreshed = true;
            }
            else
            {
              $gift2Refreshed = false;
            }
          }
          else
          {
            $gift2Refreshed = false;
          }

          if ($gift2Refreshed)
          {
            $item2Title = $gift2['Title'];
            $item2PageURL = $gift2['PageURL'];
            $item2ImageURL = $gift2['ImageURL'];
          }
          else
          {
            $response = $amazonEcs->responseGroup('Small,Images')->lookup($gift2['ASIN']);

            if (isset($response['Items']['Item']) ) {
              $item2 = $response['Items']['Item'];

              if (isset($item2['ASIN'])) {
                if (isset($item2['DetailPageURL'])) {
                  if (isset($item2['ItemAttributes']['Title'])) {
                    $item2PageURL = $item2['DetailPageURL'];
                    $item2Title = $item2['ItemAttributes']['Title'];
                  }

                  if (isset($item2['LargeImage']['URL'] )) {
                    $item2ImageURL = $item2['LargeImage']['URL'];
                  }
                }
              }

              $query = "UPDATE products SET Title = '" . str_replace("'", "\'", $item2Title) . "', PageURL = '" . str_replace("'", "\'", $item2PageURL) . "', ImageURL = '" . str_replace("'", "\'", $item2ImageURL) . "', timestamp = '" . time() . "' WHERE ASIN = '" . $gift2['ASIN'] . "'";
              $result = mysql_query($query) or die('Query failed: ' . mysql_error());
            }
          }
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
        <div class="navbar navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <a class="brand" href="index.php">RankGifts</a>
                    <ul class="nav">
                        <li><a href="ranks.php">Top 10 Gifts</a></li>
                    </ul>
                    <form class="navbar-form pull-right" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                      <input type="text" class="span2" name="add-ASIN" maxlength="10" placeholder="Enter ASIN" rel="popover" title="Finding an ASIN" data-content="Go to a product on Amazon.com and look in the URL: www.amazon.com/dp/ASIN">
                      <button type="submit" class="btn">Add Gift</button>
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
                  echo '<div class="alert alert-error"><button data-dismiss="alert" class="close" type="button">×</button><strong>Invalid ASIN:</strong> Please try again. :(<br><strong>Example:</strong> The 10-character ID after "/dp/" is the ASIN.<br>http://www.amazon.com/StarCraft-II-Heart-Collectors-PC-Mac/dp/<strong>B0050SZ1JW</strong>/</div>';
                }
                else if ($added)
                {
                  echo '<div class="alert alert-success"><button data-dismiss="alert" class="close" type="button">×</button><strong>Success!</strong> Thank you for adding a gift! :)</div>';
                }
                ?>
                <h2>Which gift would you prefer?</h2>
                <div class="row">
                  <div class="select-gift span5">
                    <?php
                      echo "<div class='lead'><a href='" . $item1PageURL . "' target='_blank'>" . truncate_title($item1Title, 100) . "</a></div>";
                      echo "<a href='" . $item1PageURL . "' target='_blank'><img src='" . $item1ImageURL . "'></a>";
                    ?>
                  </div>
                  <div class="select-gift span5">
                    <?php
                      echo "<div class='lead'><a href='" . $item2PageURL . "' target='_blank'>" . truncate_title($item2Title, 100) . "</a></div>";
                      echo "<a href='" . $item2PageURL . "' target='_blank'><img src='" . $item2ImageURL . "'></a>";
                    ?>
                  </div>
                </div>
                <div class="row">
                  <div class="span6">
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                      <input type="hidden" name="update-ASIN" value="<?php echo $gift1['ASIN']; ?>" />
                      <button type="submit" class="btn btn-info btn-large select-btn">This one looks good.</button>
                    </form>
                  </div>
                  <div class="span6">
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                      <input type="hidden" name="update-ASIN" value="<?php echo $gift2['ASIN']; ?>" />
                      <button type="submit" class="btn btn-info btn-large select-btn">I would rather have this.</button>
                    </form>
                  </div>
                </div>
            </div>
            <div id="push"></div>
        </div>
        <div id="footer">
            <div class="container">
                <div class="row">
                    <div class="span4">
                        <p>RankGifts created by <a href="http://dicksontse.com" target="_blank">Dickson Tse</a></p>
                    </div>
                    <div id="notice" class="span8">
                        <p>Gift titles/images are Amazon affiliate links.</p>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

<!DOCTYPE html>
<html>
    <head>
        <meta charset='utf-8'>
        <title>RankGifts</title>
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

        if ($_POST['ASIN'])
        {
          $query = "UPDATE products SET points = points + 1 WHERE ASIN = '" . $_POST['ASIN'] . "'";
          $result = mysql_query($query) or die('Query failed: ' . mysql_error());
        }

        $query = 'SELECT * FROM products ORDER BY RAND() LIMIT 2';
        $result = mysql_query($query) or die('Query failed: ' . mysql_error());

        $gift1 = mysql_fetch_array($result);
        $gift2 = mysql_fetch_array($result);

        mysql_free_result($result);
        mysql_close($link);

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
        ?>
    </head>
    <body>
        <div class="navbar navbar-fixed-top navbar-inverse">
            <div class="navbar-inner">
                <div class="container">
                    <a class="brand" href="index.php">RankGifts</a>
                    <ul class="nav">
                        <li><a href="#">Top 100</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div id="wrap">
            <div id="main" class="container">
                <h2>Which gift do you prefer?</h2>
                <div class="row">
                  <div class="select-gift span5">
                    <?php
                    $response = $amazonEcs->responseGroup('Small,Images')->lookup($gift1['ASIN']);

                    if (isset($response['Items']['Item']) ) {
                      $item1 = $response['Items']['Item'];

                      if (isset($item1['ASIN'])) {
                        if (isset($item1['DetailPageURL'])) {
                          if (isset($item1['ItemAttributes']['Title'])) {
                            echo "<div class='lead'><a href='" . $item1['DetailPageURL'] . "' target='_blank'>" . $item1['ItemAttributes']['Title'] . "</a></div>";
                          }

                          if (isset($item1['LargeImage']['URL'] )) {
                            echo "<a href='" . $item1['DetailPageURL'] . "' target='_blank'><img src='" . $item1['LargeImage']['URL'] . "'></a>";
                          }
                        }
                      }
                    }
                    ?>
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                      <div class="select-btn">
                        <input type="hidden" name="ASIN" value="<?php echo $gift1['ASIN']; ?>" />
                        <button type="submit" class="btn btn-primary btn-large">This one!</button>
                      </div>
                    </form>
                  </div>
                  <div class="select-gift span5">
                    <?php
                    $response = $amazonEcs->responseGroup('Small,Images')->lookup($gift2['ASIN']);

                    if (isset($response['Items']['Item']) ) {
                      $item1 = $response['Items']['Item'];

                      if (isset($item1['ASIN'])) {
                        if (isset($item1['DetailPageURL'])) {
                          if (isset($item1['ItemAttributes']['Title'])) {
                            echo "<div class='lead'><a href='" . $item1['DetailPageURL'] . "' target='_blank'>" . $item1['ItemAttributes']['Title'] . "</a></div>";
                          }

                          if (isset($item1['LargeImage']['URL'] )) {
                            echo "<a href='" . $item1['DetailPageURL'] . "' target='_blank'><img src='" . $item1['LargeImage']['URL'] . "'></a>";
                          }
                        }
                      }
                    }
                    ?>
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                      <div class="select-btn">
                        <input type="hidden" name="ASIN" value="<?php echo $gift2['ASIN']; ?>" />
                        <button type="submit" class="btn btn-primary btn-large">This one!</button>
                      </div>
                    </form>
                  </div>
                </div>
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

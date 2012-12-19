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
                    $response = $amazonEcs->responseGroup('Small,Images')->lookup('1616550414');

                    if (isset($response['Items']['Item']) ) {
                      $item1 = $response['Items']['Item'];

                      if (isset($item1['ASIN'])) {
                        if (isset($item1['DetailPageURL'])) {
                          if (isset($item1['ItemAttributes']['Title'])) {
                            echo "<div class='lead'><a href='" . $item1['DetailPageURL'] . "' target='_blank'>" . $item1['ItemAttributes']['Title'] . "</a></div>";
                          }

                          if (isset($item1['LargeImage']['URL'] )) {
                            echo "<img src='" . $item1['LargeImage']['URL'] . "'>";
                          }
                        }
                      }
                    }
                    ?>
                    <div class="select-btn">
                      <a class="btn btn-primary btn-large">This one!</a>
                    </div>
                  </div>
                  <div class="select-gift span5">
                    <?php
                    $response = $amazonEcs->responseGroup('Small,Images')->lookup('0395177111');

                    if (isset($response['Items']['Item']) ) {
                      $item1 = $response['Items']['Item'];

                      if (isset($item1['ASIN'])) {
                        if (isset($item1['DetailPageURL'])) {
                          if (isset($item1['ItemAttributes']['Title'])) {
                            echo "<div class='lead'><a href='" . $item1['DetailPageURL'] . "' target='_blank'>" . $item1['ItemAttributes']['Title'] . "</a></div>";
                          }

                          if (isset($item1['LargeImage']['URL'] )) {
                            echo "<img src='" . $item1['LargeImage']['URL'] . "'>";
                          }
                        }
                      }
                    }
                    ?>
                    <div class="select-btn">
                      <a class="btn btn-primary btn-large">This one!</a>
                    </div>
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

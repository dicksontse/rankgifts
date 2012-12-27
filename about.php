<!DOCTYPE html>
<html>
    <head>
        <meta charset='utf-8'>
        <meta name="description" content="Which gift would you prefer? Rank and add gifts while discovering new gift ideas.">
        <meta name="keywords" content="rank gift,rank gifts,gifts,gift,gift ideas,top gifts">
        <meta name="robots" content="index,follow">
        <title>RankGifts | About</title>
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/main.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script type="text/javascript">

          var _gaq = _gaq || [];
          _gaq.push(['_setAccount', 'UA-37299618-1']);
          _gaq.push(['_trackPageview']);

          (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
          })();

        </script>
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

        $link = mysql_connect(DB_SERVER, DB_USER, DB_PW) or die('Could not connect: ' . mysql_error());
        mysql_select_db(DB_NAME) or die('Could not select database');

        require 'lib/AmazonECS.class.php';

        try
        {
          $amazonEcs = new AmazonECS(AWS_API_KEY, AWS_API_SECRET_KEY, 'COM', AWS_ASSOCIATE_TAG);
          $amazonEcs->setReturnType(AmazonECS::RETURN_TYPE_ARRAY);
          $amazonEcs->requestDelay(true);
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
                        <li class="active"><a href="about.php">About</a></li>
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
                <div class="hero-unit">
                  <h2>Hello!</h2>
                  <p>RankGifts aims to rank and discover the best gifts.</p>
                  <p>It uses a simple system where you select the gift you prefer out of a random pairing. The more times a specific gift is selected, the higher the rank it will be.</p>
                  <p>Feel free to add more gifts by looking up a product on <a href="http://www.amazon.com/" target="_blank">Amazon.com</a> and entering the ASIN into the field above.</p>
                  <pre><strong>Example:</strong> The 10-character ID after "/dp/" is the ASIN.<br>http://www.amazon.com/StarCraft-II-Heart-Collectors-PC-Mac/dp/<strong>B0050SZ1JW</strong>/</pre>
                  <p>Thanks, I greatly appreciate it!</p>
                  <br>
                  <p>If you have any feedback or concerns, please email me at <a href="mailto:hello@dicksontse.com">hello@dicksontse.com</a>.</p>
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

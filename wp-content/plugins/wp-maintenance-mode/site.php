<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Coming Soon - All New KCMO.gov</title>

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

        <!-- Custom font from Google Web Fonts -->
        <link href="//fonts.googleapis.com/css?family=PT+Sans:700,400&subset=cyrillic" rel="stylesheet">

        <!-- Bootstrap stylesheets -->
        <link href="http://luminopolis.github.io/kcmo.gov/css/bootstrap.min.css" rel="stylesheet">

        <!-- Template stylesheet -->
        <link href="http://luminopolis.github.io/kcmo.gov/css/sunrise.css" rel="stylesheet">
    </head>

    <body>
        <div class="container">
            <!-- Page heading -->
            <h1 class="page-heading">KCMO.gov is launching soon</h1>
            <!-- /Page heading -->

            <!-- Description -->
            <p class="description">Coming 01.10.14. An all new city website.<br>Built using open source. Of, by, and for the people.</p>
            <!-- /Description -->

          

            <!-- Countdown -->
            <div id="countdown" class="countdown">
                <!-- Days -->
                <div class="countdown-item">
                    <div class="countdown-number countdown-days"></div>
                    <div class="countdown-text">days</div>
                </div>
                <!-- /Days -->

                <!-- Hours -->
                <div class="countdown-item">
                    <div class="countdown-number countdown-hours"></div>
                    <div class="countdown-text">hours</div>
                </div>
                <!-- /Hours -->

                <!-- Minutes -->
                <div class="countdown-item">
                    <div class="countdown-number countdown-minutes"></div>
                    <div class="countdown-text">minutes</div>
                </div>
                <!-- /Minutes -->

                <!-- Seconds -->
                <div class="countdown-item">
                    <div class="countdown-number countdown-seconds"></div>
                    <div class="countdown-text">seconds</div>
                </div>
                <!-- /Seconds -->
            </div>
            <!-- /Countdown -->
        </div>

        <!-- Scripts -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/jquery.min.js"><\/script>')</script>
        <script src="http://luminopolis.github.io/kcmo.gov/js/jquery.countdown.js"></script>
        <script type="text/javascript" charset="utf-8">
        var config = {

            countdown: {
                year: 2014,
                month: 01,
                day: 10,
                hours: 01,
                minutes: 00,
                seconds: 00
            },
        }
        </script>
        <script type="text/javascript" charset="utf-8">
        $(function() {

            /*
                Countdown
            =================================================================*/

            var date = new Date(config.countdown.year,
                                config.countdown.month - 1,
                                config.countdown.day,
                                config.countdown.hours,
                                config.countdown.minutes,
                                config.countdown.seconds),
                $body = $('body'),
                $countdown = $('#countdown');

            $countdown.countdown(date, function(event) {
                if (event.type == 'finished') {
                    $countdown.fadeOut();
                } else {
                    $('.countdown-' + event.type, $countdown).text(event.value);
                }
            });

        });
        </script>
    </body>
</html>

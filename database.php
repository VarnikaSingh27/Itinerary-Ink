<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projectdsw";
$itineraryHTML = ""; // Store itinerary output here

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['s1'])) {
    $destination = $_POST['destination'];
    $interests = isset($_POST['interests']) ? $_POST['interests'] : [];
    $start_date = $_POST['startDate'];
    $end_date = $_POST['endDate'];

    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $days_difference = $start->diff($end)->days + 1;

    if (!empty($interests)) {
        $interest_list = "'" . implode("','", array_map(function ($item) use ($conn) {
            return mysqli_real_escape_string($conn, $item);
        }, $interests)) . "'";
        $interest_filter = "AND Interest IN ($interest_list)";
    } else {
        $interest_filter = "";
    }

    $sql = "SELECT * FROM itinerary 
            WHERE City = '" . mysqli_real_escape_string($conn, $destination) . "' 
            $interest_filter 
            AND Day BETWEEN 1 AND $days_difference";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $itineraryHTML .= '<div id="itinerary"><h2>Your Personalized Itinerary</h2>';
        $currentDay = 0;
        $firstItem = true;
        while ($row = mysqli_fetch_assoc($result)) {
            if ($currentDay != $row['Day']) {
                if (!$firstItem) {
                    $itineraryHTML .= '</div>';
                }
                $firstItem = false;
                $currentDay = $row['Day'];
                $itineraryHTML .= '<h3>Day ' . htmlspecialchars($currentDay) . '</h3>';
                $itineraryHTML .= '<div class="itinerary-row">';
            }

            $itineraryHTML .= '<div class="itinerary-item">';
            $itineraryHTML .= '<h3>' . htmlspecialchars($row['Place']) . '</h3>';
            $itineraryHTML .= '<img src="' . htmlspecialchars($row['Image']) . '" alt="' . htmlspecialchars($row['Place']) . '">';
            $itineraryHTML .= '<p>' . htmlspecialchars($row['Description']) . '</p>';
            $itineraryHTML .= '</div>';
        }
        $itineraryHTML .= '</div></div>';
    } else {
        $itineraryHTML = '<p>No itinerary found for the given inputs. Please try again!</p>';
    }

    mysqli_close($conn);
}
?>

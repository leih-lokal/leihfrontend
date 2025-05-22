<?php

return function ($kirby) {
  // Default controller for normal page viewing
  if ($kirby->request()->is('GET')) {
    return [];
  }
  
  // Handle POST requests for iCal creation
  if ($kirby->request()->is('POST') && get('ical') === 'true') {
    try {
      // Get event details from the form
      $title = get('title');
      $description = get('description');
      $location = get('location');
      $startDate = get('start_date');
      $endDate = get('end_date');
      
      // Basic validation
      if (empty($title) || empty($startDate)) {
        throw new Exception('Missing required fields (title or start date)');
      }
      
      // Convert date strings to DateTime objects
      $start = new DateTime($startDate);
      $end = !empty($endDate) ? new DateTime($endDate) : clone $start;
      
      // If no end date is provided, set it to 1 hour after start
      if (empty($endDate)) {
        $end->modify('+1 hour');
      }
      
      // Generate a unique ID for the event
      $uid = md5($title . $startDate . rand(0, 10000)) . '@leihlokal.de';
      
      // Format date for iCal
      $dtstart = $start->format('Ymd\THis\Z');
      $dtend = $end->format('Ymd\THis\Z');
      $created = date('Ymd\THis\Z');
      
      // Sanitize text fields
      $title = str_replace(["\r", "\n", ",", ";"], [" ", " ", " ", " "], $title);
      $description = str_replace(["\r", "\n", ",", ";"], [" ", " ", " ", " "], $description);
      $location = str_replace(["\r", "\n", ",", ";"], [" ", " ", " ", " "], $location);
      
      // Create iCal content
      $ical = "BEGIN:VCALENDAR\r\n";
      $ical .= "VERSION:2.0\r\n";
      $ical .= "PRODID:-//Leihlokal//Events Calendar//DE\r\n";
      $ical .= "CALSCALE:GREGORIAN\r\n";
      $ical .= "METHOD:PUBLISH\r\n";
      $ical .= "BEGIN:VEVENT\r\n";
      $ical .= "UID:" . $uid . "\r\n";
      $ical .= "DTSTAMP:" . $created . "\r\n";
      $ical .= "DTSTART:" . $dtstart . "\r\n";
      $ical .= "DTEND:" . $dtend . "\r\n";
      $ical .= "SUMMARY:" . $title . "\r\n";
      
      if (!empty($description)) {
        $ical .= "DESCRIPTION:" . $description . "\r\n";
      }
      
      if (!empty($location)) {
        $ical .= "LOCATION:" . $location . "\r\n";
      }
      
      $ical .= "BEGIN:VALARM\r\n";
      $ical .= "ACTION:DISPLAY\r\n";
      $ical .= "DESCRIPTION:Reminder\r\n";
      $ical .= "TRIGGER:-PT1H\r\n"; // 1 hour before
      $ical .= "END:VALARM\r\n";
      $ical .= "END:VEVENT\r\n";
      $ical .= "END:VCALENDAR\r\n";
      
      // Set headers and output the ical content
      header('Content-Type: text/calendar; charset=utf-8');
      header('Content-Disposition: attachment; filename="' . str_replace(' ', '_', $title) . '.ics"');
      
      echo $ical;
      exit;
      
    } catch (Exception $e) {
      // Log the error
      error_log('iCal generation error: ' . $e->getMessage());
      
      // Return error as a download (for debugging)
      header('Content-Type: text/plain; charset=utf-8');
      header('Content-Disposition: attachment; filename="error.txt"');
      echo "Error generating iCal file: " . $e->getMessage() . "\n\n";
      echo "Debug information:\n";
      echo "POST data: " . print_r($_POST, true);
      exit;
    }
  }
  
  return [];
}; 
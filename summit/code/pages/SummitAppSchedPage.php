<?php

/**
 * Class SummitAppSchedPage
 */
class SummitAppSchedPage extends SummitPage {

}

class SummitAppSchedPage_Controller extends SummitPage_Controller {

    static $allowed_actions = array(
        'ViewEvent',
        'ViewSpeakerProfile',
        'ViewAttendeeProfile',
        'DoGlobalSearch',
    );

    static $url_handlers = array(
        'event/$EVENT_ID/$EVENT_TITLE' => 'ViewEvent',
        'speaker/$SPEAKER_ID'          => 'ViewSpeakerProfile',
        'attendee/$ATTENDEE_ID'        => 'ViewAttendeeProfile',
        'global-search'                => 'DoGlobalSearch',
    );

    public function init() {
        
        $this->top_section = 'full';
        $this->event_repository = new SapphireSummitEventRepository();
        $this->speaker_repository = new SapphirePresentationSpeakerRepository();

        parent::init();
        Requirements::css('themes/openstack/bower_assets/jquery-loading/dist/jquery.loading.min.css');
        Requirements::css('themes/openstack/bower_assets/chosen/chosen.min.css');
        Requirements::css("summit/css/schedule-grid.css");
        Requirements::javascript('themes/openstack/javascript/jquery-ajax-loader.js');
        Requirements::javascript('themes/openstack/bower_assets/chosen/chosen.jquery.min.js');
        Requirements::javascript('themes/openstack/bower_assets/jquery-validate/dist/jquery.validate.min.js');
        Requirements::javascript('themes/openstack/bower_assets/jquery-validate/dist/additional-methods.min.js');
   }

    public function ViewEvent() {
        $event_id = intval($this->request->param('EVENT_ID'));
        $this->event_id = $event_id;
        $event = $this->event_repository->getById($event_id);

        if (!isset($event)) {
            return $this->httpError(404, 'Sorry that article could not be found');
        }

        Requirements::block("summit/css/schedule-grid.css");
        Requirements::css("summit/css/summitapp-event.css");

        return $this->renderWith(array('SummitAppEventPage','SummitPage','Page'), array('Event' => $event) );
    }

    public function ViewSpeakerProfile() {
        $speaker_id = intval($this->request->param('SPEAKER_ID'));
        $speaker = $this->speaker_repository->getById($speaker_id);

        if (!isset($speaker)) {
            return $this->httpError(404, 'Sorry that speaker could not be found');
        }

        Requirements::block("summit/css/schedule-grid.css");
        Requirements::css("summit/css/summitapp-speaker.css");

        return $this->renderWith(array('SummitAppSpeakerPage','SummitPage','Page'), array('Speaker' => $speaker) );
    }

    public function ViewAttendeeProfile()
    {
        // TODO : implement view
        $attendee_id = intval($this->request->param('ATTENDEE_ID'));
        return $this->httpError(404, 'Sorry that attendee could not be found');
    }

    public function getFeedbackForm() {
        Requirements::javascript(Director::protocol()."ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js");
        Requirements::javascript(Director::protocol()."ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/additional-methods.min.js");
        Requirements::javascript("marketplace/code/ui/frontend/js/star-rating.min.js");
        Requirements::javascript("summit/javascript/summitapp-feedbackform.js");
        Requirements::css("marketplace/code/ui/frontend/css/star-rating.min.css");
        $form = new SummitEventFeedbackForm($this, 'SummitEventFeedbackForm');
        return $form;
    }

    public function getCurrentSummitEventsBy1stDate()
    {
        $summit = $this->Summit();
        return new ArrayList($summit->getSchedule($summit->getBeginDate()));
    }

    public function isEventOnMySchedule($event_id)
    {
       return SummitAppScheduleApi::isEventOnMySchedule($event_id, $this->Summit());
    }

    public function DoGlobalSearch(SS_HTTPRequest $request){
        $term      = Convert::raw2sql($request->requestVar('global-search-term'));
        $summit_id = $this->Summit()->ID;
        // TODO : move all this sql code to repository
        $sql_events = <<<SQL
    SELECT DISTINCT E.* FROM SummitEvent E
    WHERE E.SummitID = {$summit_id} AND E.Published = 1
    AND
    (
        EXISTS
        (
            SELECT S.ID FROM PresentationSpeaker S INNER JOIN Presentation_Speakers PS ON PS.PresentationSpeakerID = S.ID
            WHERE S.SummitID = {$summit_id} AND PS.PresentationID = E.ID AND (S.FirstName LIKE '%{$term}%' OR  LastName LIKE '%{$term}%' OR  Bio LIKE '%{$term}%')
        )
        OR
        EXISTS
        (
            SELECT T.ID FROM Tag T INNER JOIN SummitEvent_Tags ET ON ET.TagID = T.ID
            WHERE ET.SummitEventID = E.ID AND T.Tag LIKE '%{$term}%'
        )
        OR
        EXISTS
        (
            SELECT P.ID FROM Presentation P
            INNER JOIN PresentationCategory T ON T.ID = P.CategoryID
            WHERE P.ID = E.ID AND T.Title LIKE '%{$term}%'
        )
        OR
        Title LIKE '%{$term}%'
        OR
        Description LIKE '%{$term}%'
        OR
        ShortDescription LIKE '%{$term}%'
    ) ORDER BY E.StartDate ASC, E.EndDate ASC;
SQL;

$sql_speakers = <<<SQL
    SELECT DISTINCT S.* FROM PresentationSpeaker S
    WHERE SummitID = {$summit_id}
    AND EXISTS
    (
        SELECT P.ID From Presentation P
        INNER JOIN SummitEvent E ON E.ID = P.ID AND E.Published = 1 AND E.SummitID = {$summit_id}
        INNER JOIN Presentation_Speakers PS ON PS.PresentationID = P.ID
        WHERE PS.PresentationSpeakerID = S.ID
    )
    AND ( FirstName LIKE '%{$term}%' OR  LastName LIKE '%{$term}%' OR  Bio LIKE '%{$term}%' );
SQL;

        $speakers  = array();
        $events    = array();

        foreach(DB::query($sql_speakers) as $row)
        {
            $class = $row['ClassName'];
            array_push($speakers, new $class($row));
        }

        foreach(DB::query($sql_events) as $row)
        {
            $class = $row['ClassName'];
            array_push($events, new $class($row));
        }

        return $this->renderWith
        (
            array
            (
                'SummitAppSchedPage_globalSearchResults',
                'SummitPage',
                'Page'
            ),
            array
            (
                'SpeakerResults'   => new ArrayList($speakers),
                'EventResults'     => new ArrayList($events),
                'SearchTerm'       => $term,
            )
        );
    }
}
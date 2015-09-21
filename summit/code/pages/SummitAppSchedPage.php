<?php

class SummitAppSchedPage extends SummitPage {

    public function renderSchedule($events) {

        $ordered_events = array();

        foreach($events as $event) {
            $day = date('Ymd',strtotime($event->StartDate));
            $hour = date('His',strtotime($event->StartDate));

            if (!isset($ordered_events[$day])) {
                $ordered_events[$day] = array();
                $event->FirstOfDay = true;
            }

            if (!isset($ordered_events[$day][$hour])) {
                $ordered_events[$day][$hour] = array();
                $event->FirstOfHour = true;
            }

            $ordered_events[$day][$hour][] = $event;
        }

        $viewable_schedule = new ArrayList();
        foreach ($ordered_events as $day_array) {
            $hours_arraylist = new ArrayList();
            foreach ($day_array as $hour_array) {
                $events_arraylist = new ArrayList($hour_array);
                $hours_arraylist->push($events_arraylist);
            }
            $viewable_schedule->push($hours_arraylist);
        }

        return $this->renderWith('SummitAppSchedPage_schedule',array('Schedule'=>$viewable_schedule));
    }

    public function isAttendee($summit_id) {
        return Member::currentUser()->isAttendee($summit_id);
    }

    public function isScheduled($summit_id,$event_id) {
        $attendee = Member::currentUser()->getSummitAttendee($summit_id);

        return $attendee->isScheduled($event_id);
    }
}


class SummitAppSchedPage_Controller extends SummitPage_Controller {

    static $allowed_actions = array(
        'ViewEvent',
    );

    static $url_handlers = array(
        'event/$EVENT_ID/$EVENT_TITLE'   => 'ViewEvent',
    );

    public function init() {
        
        $this->top_section = 'full';
        $this->event_repository = new SapphireSummitEventRepository();
        parent::init();

        Requirements::javascript("summit/javascript/summitapp-schedule.js");
        Requirements::css("summit/css/summitapp-schedule.css");
	}

    function ViewEvent() {
        $event_id = intval($this->request->param('EVENT_ID'));
        $event = $this->event_repository->getById($event_id);

        if (!isset($event)) {
            return $this->httpError(404, 'Sorry that article could not be found');
        }

        return $this->renderWith(array('SummitAppEventPage','SummitPage','Page'), array('Event' => $event) );
    }

	
}
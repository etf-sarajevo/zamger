<?php

// List of services with wiring code

$wiring = array(
	array(
		"path" => "/", 
		"description" => "Top level endpoint", 
		"method" => "GET", 
		"code" => function($p) { 
			// This will produce {} in JSON so that output is valid
			return new stdClass; 
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"person" => array("href" => "person"),
			"course" => array("href" => "course"),
			"group" => array("href" => "group"),
			"class" => array("href" => "class"),
			"exam" => array("href" => "exam"),
			"homework" => array("href" => "homework"),
			"quiz" => array("href" => "quiz"),
			"event" => array("href" => "event"),
			"certificate" => array("href" => "certificate"),
		) // TODO: define identity links for classes
	),


	// INBOX
	
	array(
		"path" => "inbox", 
		"description" => "Recent personal messages", 
		"method" => "GET",
		"params" => [ 
			"messages" => [ "type" => "int", "default" => "5", "description" => "Total number of messages to show" ], 
			"start" => [ "type" => "int", "default" => "0", "description" => "Start from this message (paging support)" ] 
		],
		"code" => function($p) { 
			return Message::latest($p['messages'], $p['start']);
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"message" => array("href" => "inbox/{id}"),
			"count" => array("href" => "inbox/count"),
			"unread" => array("href" => "inbox/unread"),
			"outbox" => array("href" => "inbox/outbox"),
		) // TODO: define identity links for classes
	),
	array(
		"path" => "inbox", 
		"description" => "Send personal message", 
		"method" => "POST",
		"params" => [
			"message" => [ "type" => "object", "class" => "Message" ]
		],
		"code" => function($p) {
			$p['message']->validate(); 
			$p['message']->send(); 
			return $p['message'];
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"message" => array("href" => "inbox/{id}"),
			"count" => array("href" => "inbox/count"),
			"unread" => array("href" => "inbox/unread"),
			"outbox" => array("href" => "inbox/outbox"),
		) // TODO: define identity links for classes
	),
	array(
		"path" => "inbox/outbox", 
		"description" => "Sent messages", 
		"method" => "GET",
		"params" => [ 
			"messages" => [ "type" => "int", "default" => "5", "description" => "Total number of messages to show" ], 
			"start" => [ "type" => "int", "default" => "0", "description" => "Start from this message (paging support)" ] 
		],
		"code" => function($p) { 
			return Message::outbox($p['messages'], $p['start']);
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"inbox" => array("href" => "inbox"),
			"message" => array("href" => "inbox/{id}"),
			"count" => array("href" => "inbox/count"),
			"unread" => array("href" => "inbox/unread"),
		) // TODO: define identity links for classes
	),
	array(
		"path" => "inbox/count", 
		"description" => "Total personal messages", 
		"method" => "GET",
		"code" => "return Message::count();", 
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"inbox" => array("href" => "inbox"),
			"message" => array("href" => "inbox/{id}"),
			"unread" => array("href" => "inbox/unread"),
			"outbox" => array("href" => "inbox/outbox"),
		) // TODO: define identity links for classes
	),
	array(
		"path" => "inbox/{id}", 
		"description" => "Get message", 
		"method" => "GET", 
		"code" => function($p) { 
			return Message::fromId($p['id']);
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"inbox" => array("href" => "inbox"),
			"count" => array("href" => "inbox/count"),
			"unread" => array("href" => "inbox/unread"),
			"outbox" => array("href" => "inbox/outbox"),
		) // TODO: define identity links for classes
	),
	array(
		"path" => "inbox/unread", 
		"description" => "Unread messages", 
		"method" => "GET", 
		"code" => function($p) { 
			return Message::unread();
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"inbox" => array("href" => "inbox"),
			"count" => array("href" => "inbox/count"),
			"message" => array("href" => "inbox/{id}"),
			"outbox" => array("href" => "inbox/outbox"),
		) // TODO: define identity links for classes
	),
	
	
	// PERSON
	
	array(
		"path" => "person", 
		"description" => "Current user", 
		"method" => "GET", 
		"code" => function($p) { 
			$person = Person::fromId(Session::$userid); 
			$person->getTitles(); 
			return $person;
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"self" => array("href" => "person/[id]"),
			"personById" => array("href" => "person/{id}"),
			"personByLogin" => array("href" => "person/byLogin?login={login}"),
			"search" => array("href" => "person/search?query={query}")
		) // TODO: define identity links for classes
	),
	array(
		"path" => "person/{id}", 
		"description" => "Find user by id", 
		"method" => "GET", 
		"code" => function($p) { 
			$person = Person::fromId($p['id']); 
			$person->getTitles(); 
			return $person;
		},
		"acl" => function($p) {
			if (isset($_REQUEST['resolve']) && in_array("ExtendedPerson", $_REQUEST['resolve']))
				return AccessControl::self($p['id']) || AccessControl::privilege('studentska');
			return AccessControl::loggedIn();
		},
		"hateoas_links" => array(
			"self" => array("href" => "person/[id]"),
			"currentUser" => array("href" => "person"),
			"personByLogin" => array("href" => "person/byLogin?login={login}"),
			"search" => array("href" => "person/search?query={query}")
		)
	),
	array(
		"path" => "person/byLogin", 
		"description" => "Find user by login", 
		"method" => "GET", 
		"params" => [ 
			"login" => [ "type" => "string" ]
		],
		"code" => function($p) { 
			$person = Person::fromLogin($p['login']); 
			$person->getTitles(); 
			return $person;
		},
		"acl" => function($p) {
			if (isset($_REQUEST['resolve']) && in_array("ExtendedPerson", $_REQUEST['resolve']))
				return AccessControl::privilege('studentska');
			return AccessControl::loggedIn();
		},
		"hateoas_links" => array(
			"self" => array("href" => "person/[id]"),
			"currentUser" => array("href" => "person"),
			"personById" => array("href" => "person/{id}"),
			"search" => array("href" => "person/search?query={query}")
		)
	),
	array(
		"path" => "person/search", 
		"description" => "Search users", 
		"search" => "personById",
		"method" => "GET", 
		"params" => [ 
			"query" => [ "type" => "string" ]
		],
		"code" => function($p) { 
			return Person::search($p['query']); 
		},
		"autoresolve" => array(),
		"acl" => function($p) {
			if (isset($_REQUEST['resolve']) && in_array("ExtendedPerson", $_REQUEST['resolve']))
				return AccessControl::privilege('studentska');
			return AccessControl::loggedIn();
		},
		"hateoas_links" => array(
			"currentUser" => array("href" => "person"),
			"personByLogin" => array("href" => "person/byLogin?login={login}"),
			"personById" => array("href" => "person/{id}")
		)
	),
	
	array(
		"path" => "extendedPerson/{id}", 
		"description" => "Extended details on person (student)", 
		"method" => "GET", 
		"code" => function($p) { 
			$person = ExtendedPerson::fromId($p['id']); 
			return $person;
		},
		"autoresolve" => array(),
		"acl" => function($p) { 
			return AccessControl::self($p['id']) || AccessControl::privilege('studentska'); 
		},
	),
	
	
	
	// COURSE
	
	array(
		"path" => "course", 
		"description" => "List of hateoas links", 
		"method" => "GET", 
		"code" => function($p) { 
			return new stdClass;
		},
		"acl" => function($p) { return AccessControl::all(); },
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/programme/{programme}/{semester}'),
			'coursesForStudent' => array('href' => 'course/student/{student}'),
			'coursesForTeacher' => array('href' => 'course/teacher/{teacher}')
		)
	),

	
	array(
		"path" => "course/{id}", 
		"description" => "Course", 
		"method" => "GET", 
		"code" => function($p) { 
			$cy = AcademicYear::getCurrent(); 
			return CourseUnitYear::fromCourseAndYear($p['id'], $cy->id);
		},
		"acl" => function($p) { return AccessControl::all(); },
		"autoresolve" => array("CourseUnit", "AcademicYear", "Institution", "Scoring", "Programme", "ProgrammeType"),
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/programme/{programme}/{semester}'),
			'coursesForStudent' => array('href' => 'course/student/{student}'),
			'coursesForTeacher' => array('href' => 'course/teacher/{teacher}')
		)
	),
	
	array(
		"path" => "course/{id}/{year}", 
		"description" => "Course", 
		"method" => "GET", 
		"code" => function($p) { 
			return CourseUnitYear::fromCourseAndYear($p['id'], $p['year']);
		},
		"acl" => function($p) { return AccessControl::all(); },
		"autoresolve" => array("CourseUnit", "AcademicYear", "Institution", "Scoring", "Programme"),
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/programme/{programme}/{semester}'),
			'coursesForStudent' => array('href' => 'course/student/{student}'),
			'coursesForTeacher' => array('href' => 'course/teacher/{teacher}')
		)
	),
	
	array(
		"path" => "course/{id}/{year}/enroll/{student}", 
		"description" => "Enroll student into course", 
		"method" => "POST", 
		"code" => function($p) { 
			$cuy = CourseUnitYear::fromCourseAndYear($p['id'], $p['year']);
			$portfolio = $cuy->enrollStudent($p['student']);
			return $portfolio;
		},
		"acl" => function($p) { return AccessControl::privilege('studentska'); },
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/programme/{programme}/{semester}'),
			'coursesForStudent' => array('href' => 'course/student/{student}'),
			'coursesForTeacher' => array('href' => 'course/teacher/{teacher}')
		)
	),
	
	array(
		"path" => "course/programme/{programme}/{semester}", 
		"description" => "List of courses on programme and semester", 
		"method" => "GET", 
		"code" => function($p) { 
			$cy = AcademicYear::getCurrent();
			return CourseOffering::getCoursesOffered($cy->id, $p['programme'], $p['semester']);
		},
		"acl" => function($p) { return AccessControl::all(); },
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/programme/{programme}/{semester}'),
			'coursesForStudent' => array('href' => 'course/student/{student}'),
			'coursesForTeacher' => array('href' => 'course/teacher/{teacher}')
		)
	),

	array(
		"path" => "course/teacher/{teacher}", 
		"description" => "List of courses for teacher", 
		"method" => "GET", 
		"code" => function($p) { 
			return CourseUnitYear::forTeacher($p['teacher']);
		},
		"acl" => function($p) { 
			return AccessControl::self($p['teacher']) || AccessControl::privilege('studentska'); 
		},
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/programme/{programme}/{semester}'),
			'coursesForStudent' => array('href' => 'course/student/{student}'),
			'coursesForTeacher' => array('href' => 'course/teacher/{teacher}')
		)
	),

	array(
		"path" => "course/teacher/{teacher}/{year}", 
		"description" => "List of courses for teacher in academic year", 
		"method" => "GET", 
		"code" => function($p) { 
			return CourseUnitYear::forTeacher($p['teacher'], $p['year']);
		},
		"acl" => function($p) { 
			return AccessControl::self($p['teacher']) || AccessControl::privilege('studentska'); 
		},
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/programme/{programme}/{semester}'),
			'coursesForStudent' => array('href' => 'course/student/{student}'),
			'coursesForTeacher' => array('href' => 'course/teacher/{teacher}')
		)
	),
	
	array(
		"path" => "course/student/{student}", 
		"description" => "List current courses for student", 
		"method" => "GET", 
		"params" => [ 
			"all" => [ "type" => "bool", "default" => "false", "description" => "If true, return all courses the student was ever enrolled into, otherwise just the ones in current year/semester" ]
		],
		"code" => function($p) { 
			if ($p['all']) 
				return Portfolio::getAllForStudent($p['student'], true, true); 
			return Portfolio::getCurrentForStudent($p['student'], true, true);
		},
		"acl" => function($p) { 
			return AccessControl::self($p['student']) || AccessControl::privilege('studentska'); 
		},
		"autoresolve" => array("CourseOffering", "AcademicYear", "CourseUnit", "Programme"),
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/programme/{programme}/{semester}'),
			'coursesForStudent' => array('href' => 'course/student/{student}'),
			'coursesForTeacher' => array('href' => 'course/teacher/{teacher}')
		)
	),
	
	array(
		"path" => "course/{course}/student/{student}", 
		"description" => "Details of specific course for student", 
		"method" => "GET", 
		"params" => [ 
			"year" => [ "type" => "int", "default" => "0", "description" => "Academic year of course (if 0 or ommitted, the current year will be used)" ]
		],
		"code" => function($p) { 
			$portfolio = Portfolio::fromCourseUnit($p['student'], $p['course'], $p['year']); 
			$portfolio->getScore();
			$portfolio->getGrade(); 
			return $portfolio;
		},
		"acl" => function($p) { 
			return AccessControl::self($p['student']) || AccessControl::privilege('studentska')
				|| teacherLevel($p['course'], $p['year']); 
		},
		"autoresolve" => array(),
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/programme/{programme}/{semester}'),
			'coursesForStudent' => array('href' => 'course/student/{student}'),
			'coursesForTeacher' => array('href' => 'course/teacher/{teacher}')
		)
	),
	
	
	
	// GROUP
	
	array(
		"path" => "group", 
		"description" => "List of hateoas links", 
		"method" => "GET", 
		"code" => function($p) { 
			return new stdClass;
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"group" => array("href" => "group/{id}"),
			"allGroups" => array("href" => "group/course/{course}/?year={year}"),
			"allStudents" => array("href" => "group/course/{course}/allStudents/?year={year}"),
			"forStudent" => array("href" => "group/course/{course}/student/{student}/?year={year}"),
		)
	),
	
	array(
		"path" => "group/{id}", 
		"description" => "Get group with id", 
		"method" => "GET", 
		"params" => [ 
			"details" => [ "type" => "bool", "default" => "false", "description" => "Show detailed scores for students on various activities" ], 
			"names" => [ "type" => "bool", "default" => "false", "description" => "Resolve Person details for students in groups" ] 
		],
		"code" => function($p) { 
			return Group::fromId($p['id'], $p['details'], true, $p['names']);
		},
		"acl" => function($p) { 
			return AccessControl::teacherLevelGroup($p['id']); 
		},
		"autoresolve" => array(),
		"hateoas_links" => array(
			"group" => array("href" => "group/{id}"),
			"allGroups" => array("href" => "group/course/{course}/?year={year}"),
			"allStudents" => array("href" => "group/course/{course}/allStudents/?year={year}"),
			"forStudent" => array("href" => "group/course/{course}/student/{student}/?year={year}"),
		)
	),
	
	array(
		"path" => "group/{id}/student/{student}", 
		"description" => "Check if student is enrolled in group", 
		"method" => "GET", 
		"code" => function($p) { 
			$grp = Group::fromId($p['id']);
			return $grp->isMember($p['student']);
		},
		"acl" => function($p) { 
			return AccessControl::teacherLevelGroup($p['id']); 
		},
		"autoresolve" => array(),
		"hateoas_links" => array(
			"group" => array("href" => "group/{id}"),
			"allGroups" => array("href" => "group/course/{course}/?year={year}"),
			"allStudents" => array("href" => "group/course/{course}/allStudents/?year={year}"),
			"forStudent" => array("href" => "group/course/{course}/student/{student}/?year={year}"),
		)
	),
	
	array(
		"path" => "group/{id}/student/{student}", 
		"description" => "Enroll student in group (removing from other groups)", 
		"method" => "PUT", 
		"code" => function($p) { 
			$grp = Group::fromId($p['id']);
			return $grp->addMember($p['student'], true);
		},
		"acl" => function($p) { 
			return AccessControl::teacherLevelGroup($p['id']); 
		},
		"autoresolve" => array(),
		"hateoas_links" => array(
			"group" => array("href" => "group/{id}"),
			"allGroups" => array("href" => "group/course/{course}/?year={year}"),
			"allStudents" => array("href" => "group/course/{course}/allStudents/?year={year}"),
			"forStudent" => array("href" => "group/course/{course}/student/{student}/?year={year}"),
		)
	),
	
	array(
		"path" => "group/{id}/student/{student}", 
		"description" => "Enroll student in group (not removing from other groups)", 
		"method" => "POST", 
		"code" => function($p) { 
			$grp = Group::fromId($p['id']);
			return $grp->addMember($p['student'], false);
		},
		"acl" => function($p) { 
			return AccessControl::teacherLevelGroup($p['id']); 
		},
		"autoresolve" => array(),
		"hateoas_links" => array(
			"group" => array("href" => "group/{id}"),
			"allGroups" => array("href" => "group/course/{course}/?year={year}"),
			"allStudents" => array("href" => "group/course/{course}/allStudents/?year={year}"),
			"forStudent" => array("href" => "group/course/{course}/student/{student}/?year={year}"),
		)
	),
	
	array(
		"path" => "group/{id}/student/{student}", 
		"description" => "Disenroll student from group", 
		"method" => "DELETE", 
		"code" => function($p) { 
			$grp = Group::fromId($p['id']);
			return $grp->removeMember($p['student']);
		},
		"acl" => function($p) { 
			return AccessControl::teacherLevelGroup($p['id']); 
		},
		"autoresolve" => array(),
		"hateoas_links" => array(
			"group" => array("href" => "group/{id}"),
			"allGroups" => array("href" => "group/course/{course}/?year={year}"),
			"allStudents" => array("href" => "group/course/{course}/allStudents/?year={year}"),
			"forStudent" => array("href" => "group/course/{course}/student/{student}/?year={year}"),
		)
	),
	
	array(
		"path" => "group/course/{course}", 
		"description" => "Get list of student groups on course", 
		"method" => "GET", 
		"params" => [ 
			"year" => [ "type" => "int", "default" => "0", "description" => "Academic year (if ommitted or 0, current year is used)" ], 
			"includeVirtual" => [ "type" => "bool", "default" => "false", "description" => "Should the virtual group be included" ],
			"getMembers" => [ "type" => "bool", "default" => "false", "description" => "Get members for each group as well" ]
		],
		"code" => function($p) { 
			return Group::forCourseAndYear($p['course'], $p['year'], $p['includeVirtual'], $p['getMembers']);
		},
		"acl" => function($p) { 
			return AccessControl::teacherLevel($p['course'], $p['year']); 
		},
		"autoresolve" => array(),
		"hateoas_links" => array(
			"group" => array("href" => "group/{id}"),
			"allGroups" => array("href" => "group/course/{course}/?year={year}"),
			"allStudents" => array("href" => "group/course/{course}/allStudents/?year={year}"),
			"forStudent" => array("href" => "group/course/{course}/student/{student}/?year={year}"),
		)
	),
	
	array(
		"path" => "group/course/{course}/allStudents", 
		"description" => "Get all students on course (virtual group)", 
		"method" => "GET", 
		"params" => [ 
			"year" => [ "type" => "int", "default" => "0", "description" => "Academic year (if ommitted or 0, current year is used)" ], 
			"details" => [ "type" => "bool", "default" => "false", "description" => "Show detailed scores for students on various activities" ], 
			"names" => [ "type" => "bool", "default" => "false", "description" => "Resolve Person details for students in groups" ] 
		],
		"code" => function($p) { 
			return Group::virtualForCourse($p['course'], $p['year'], $p['details'], true, $p['names']);
		},
		"acl" => function($p) { 
			// FIXME use teacherLevelGroup and id of virtual group
			return AccessControl::teacherLevel($p['course'], $p['year']); 
		},
		"autoresolve" => array(),
		"hateoas_links" => array(
			"group" => array("href" => "group/{id}"),
			"allGroups" => array("href" => "group/course/{course}/?year={year}"),
			"allStudents" => array("href" => "group/course/{course}/allStudents/?year={year}"),
			"forStudent" => array("href" => "group/course/{course}/student/{student}/?year={year}"),
		)
	),
	
	array(
		"path" => "group/course/{course}/student/{student}", 
		"description" => "Get the list of groups that a student belongs to for given course (use student id 0 for current user)", 
		"method" => "GET", 
		"params" => [ 
			"year" => [ "type" => "int", "default" => "0", "description" => "Academic year (if ommitted or 0, current year is used)" ]
		],
		"code" => function($p) { 
			if ($p['student'] == 0) 
				$p['student'] = Session::$userid; 
			return Group::fromStudentAndCourse($p['student'], $p['course'], $p['year']);
		},
		"acl" => function($p) { 
			return AccessControl::self($p['student']) || AccessControl::teacherLevel($p['course'], $p['year']); 
		},
		"autoresolve" => array(),
		"hateoas_links" => array(
			"group" => array("href" => "group/{id}"),
			"allGroups" => array("href" => "group/course/{course}/?year={year}"),
			"allStudents" => array("href" => "group/course/{course}/allStudents/?year={year}"),
			"forStudent" => array("href" => "group/course/{course}/student/{student}/?year={year}"),
		)
	),
	
	
	
	// COMMENT
	
	array(
		"path" => "comment", 
		"description" => "List of hateoas links", 
		"method" => "GET", 
		"code" => function($p) { 
			return new stdClass;
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"comment" => array("href" => "comment/{id}"),
			"allCommentsForStudent" => array("href" => "comment/group/{group}/student/{student}"),
		)
	),
	
	array(
		"path" => "comment/{id}", 
		"description" => "Get comment with specific ID", 
		"method" => "GET", 
		"code" => function($p) { 
			return Comment::fromId($p['id']);
		},
		"acl" => function($p) {
			$com = Comment::fromId($p['id']); // Reuse this in "code" somehow?
			return AccessControl::teacherLevelGroup($com->Group->id); 
		},
		"hateoas_links" => array(
			"comment" => array("href" => "comment/{id}"),
			"allCommentsForStudent" => array("href" => "comment/group/{group}/student/{student}"),
		)
	),
	
	array(
		"path" => "comment/{id}", 
		"description" => "Delete comment", 
		"method" => "DELETE", 
		"code" => function($p) { 
			$com = Comment::fromId($p['id']);
			$com->delete();
		},
		"acl" => function($p) {
			$com = Comment::fromId($p['id']); // Reuse this in "code" somehow?
			return AccessControl::teacherLevelGroup($com->Group->id); 
		},
		"hateoas_links" => array(
			"comment" => array("href" => "comment/{id}"),
			"allCommentsForStudent" => array("href" => "comment/group/{group}/student/{student}"),
		)
	),
	
	array(
		"path" => "comment/{id}", 
		"description" => "Update comment", 
		"method" => "PUT", 
		"params" => [
			"comment" => [ "type" => "object", "class" => "Comment" ]
		],
		"code" => function($p) { 
			$com = $p['comment'];
			$com->teacher->id = Session::$userid; 
			$com->validate(); 
			$com->update();
		},
		"acl" => function($p) {
			$com = Comment::fromId($p['id']); // Reuse this in "code" somehow?
			return AccessControl::teacherLevelGroup($com->Group->id); 
		},
		"hateoas_links" => array(
			"comment" => array("href" => "comment/{id}"),
			"allCommentsForStudent" => array("href" => "comment/group/{group}/student/{student}"),
		)
	),
	
	array(
		"path" => "comment/group/{group}/student/{student}", 
		"description" => "Get all comments on student activity in group", 
		"method" => "GET", 
		"code" => function($p) { 
			return Comment::forStudentInGroup($p['student'], $p['group']);
		},
		"acl" => function($p) { 
			return AccessControl::teacherLevelGroup($p['group']); 
		},
		"autoresolve" => array(),
		"hateoas_links" => array(
			"comment" => array("href" => "comment/{id}"),
			"allCommentsForStudent" => array("href" => "comment/group/{group}/student/{student}"),
		)
	),
	
	array(
		"path" => "comment/group/{group}/student/{student}", 
		"description" => "Add new comment on student activity in group", 
		"method" => "POST", 
		"params" => [
			"comment" => [ "type" => "object", "class" => "Comment" ]
		],
		"code" => function($p) { 
			$com = $p['comment'];
			$com->teacher->id = Session::$userid; 
			$com->validate(); 
			$com->add();
		},
		"acl" => function($p) { 
			return AccessControl::teacherLevelGroup($p['group']); 
		},
		"autoresolve" => array(),
		"hateoas_links" => array(
			"comment" => array("href" => "comment/{id}"),
			"allCommentsForStudent" => array("href" => "comment/group/{group}/student/{student}"),
		)
	),
	
	
	// CLASS/ATTENDANCE
	
	array(
		"path" => "class", 
		"description" => "List of hateoas links", 
		"method" => "GET", 
		"code" => function($p) { 
			return new stdClass;
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"class" => array("href" => "class/{id}"),
			"allClassesInGroup" => array("href" => "class/group/{group}"),
			"attendance" => array("href" => "class/{id}/student/{student}"),
			"attendanceOnCourse" => array("href" => "class/course/{course}/student/{student}"),
		)
	),
	
	array(
		"path" => "class/{id}", 
		"description" => "Get class information", 
		"method" => "GET", 
		"code" => function($p) { 
			return ZClass::fromId($p['id']);
		},
		"acl" => function($p) { 
			$zclass = ZClass::fromId($p['id']); // Reuse this in "code" somehow?
			return AccessControl::teacherLevelGroup($zclass->Group->id); 
		},
		"hateoas_links" => array(
			"class" => array("href" => "class/{id}"),
			"allClassesInGroup" => array("href" => "class/group/{group}"),
			"attendance" => array("href" => "class/{id}/student/{student}"),
			"attendanceOnCourse" => array("href" => "class/course/{course}/student/{student}"),
		)
	),
	
	array(
		"path" => "class/group/{group}", 
		"description" => "List of classes registered for group", 
		"method" => "GET", 
		"code" => function($p) { 
			return ZClass::fromGroup($p['group']);
		},
		"acl" => function($p) { 
			return AccessControl::teacherLevelGroup($p['group']); 
		},
		"hateoas_links" => array(
			"class" => array("href" => "class/{id}"),
			"allClassesInGroup" => array("href" => "class/group/{group}"),
			"attendance" => array("href" => "class/{id}/student/{student}"),
			"attendanceOnCourse" => array("href" => "class/course/{course}/student/{student}"),
		)
	),
	
	array(
		"path" => "class/{id}/student/{student}", 
		"description" => "Get information on attendance of student", 
		"method" => "GET", 
		"code" => function($p) { 
			$att = Attendance::fromStudentAndClass($p['student'], $p['id']);
			$att->getPresence();
			return $att;
		},
		"acl" => function($p) { 
			$zclass = ZClass::fromId($p['id']);
			return AccessControl::teacherLevelGroup($zclass->Group->id); 
		},
		"hateoas_links" => array(
			"class" => array("href" => "class/{id}"),
			"allClassesInGroup" => array("href" => "class/group/{group}"),
			"attendanceOnCourse" => array("href" => "class/course/{course}/student/{student}"),
		)
	),
	
	array(
		"path" => "class/{id}/student/{student}", 
		"description" => "Update attendance of student", 
		"method" => "POST", 
		"params" => [
			"att" => [ "type" => "object", "class" => "Attendance" ]
		],
		"code" => function($p) { 
			$att = $p['att'];
			$att->student->id = $p['student']; 
			$att->ZClass->id = $p['id'];
			$att->setPresence($att->presence); 
			return $att;
		},
		"acl" => function($p) { 
			$zclass = ZClass::fromId($p['id']);
			return AccessControl::teacherLevelGroup($zclass->Group->id); 
		},
		"hateoas_links" => array(
			"class" => array("href" => "class/{id}"),
			"allClassesInGroup" => array("href" => "class/group/{group}"),
			"attendanceOnCourse" => array("href" => "class/course/{course}/student/{student}"),
		)
	),
	
	array(
		"path" => "class/{id}/student/{student}", 
		"description" => "Set attendance of student", 
		"method" => "PUT", 
		"params" => [
			"att" => [ "type" => "object", "class" => "Attendance" ]
		],
		"code" => function($p) { 
			$att = $p['att'];
			$att->student->id = $p['student']; 
			$att->ZClass->id = $p['id'];
			$att->setPresence($att->presence); 
			return $att;
		},
		"acl" => function($p) { 
			$zclass = ZClass::fromId($p['id']);
			return AccessControl::teacherLevelGroup($zclass->Group->id); 
		},
		"hateoas_links" => array(
			"class" => array("href" => "class/{id}"),
			"allClassesInGroup" => array("href" => "class/group/{group}"),
			"attendanceOnCourse" => array("href" => "class/course/{course}/student/{student}"),
		)
	),
	
	array(
		"path" => "class/{id}/student/{student}", 
		"description" => "Delete attendance of student (set it to neutral value)", 
		"method" => "DELETE", 
		"code" => function($p) { 
			$att = Attendance::fromStudentAndClass($p['student'], $p['id']);
			$att->deletePresence();
			return $att;
		},
		"acl" => function($p) { 
			$zclass = ZClass::fromId($p['id']);
			return AccessControl::teacherLevelGroup($zclass->Group->id); 
		},
		"hateoas_links" => array(
			"class" => array("href" => "class/{id}"),
			"allClassesInGroup" => array("href" => "class/group/{group}"),
			"attendanceOnCourse" => array("href" => "class/course/{course}/student/{student}"),
		)
	),
	
	array(
		"path" => "class/course/{course}/student/{student}",  // FIXME: Returns too much data when resolve[]=Group !
		"description" => "Get all attendance data for student on course", 
		"method" => "GET", 
		"params" => [ 
			"year" => [ "type" => "int", "default" => "0", "description" => "Academic year (if ommitted or 0, current year is used)" ],
			"scoringElement" => [ "type" => "int", "default" => "0", "description" => "Which activity of type 'attendance' is requested (if ommitted or 0, all activities of type 'attendance' will be returned)" ]
		],
		"code" => function($p) { 
			$att = Attendance::forStudentOnCourseUnit($p['student'], $p['course'], $p['year'], $p['scoringElement']);
			return $att;
		},
		"acl" => function($p) { 
			return AccessControl::self($p['student']) || AccessControl::privilege('studentska') // TODO: remove studentska?
				|| AccessControl::teacherLevel($p['course'], $p['year']); 
		},
		"hateoas_links" => array(
			"class" => array("href" => "class/{id}"),
			"allClassesInGroup" => array("href" => "class/group/{group}"),
			"attendance" => array("href" => "class/{id}/student/{student}"),
		)
	),
	
	
	
	// EXAM
	
	array(
		"path" => "exam", 
		"description" => "List of hateoas links", 
		"method" => "GET", 
		"code" => function($p) { 
			return new stdClass;
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"exam" => array("href" => "exam/{id}"),
			"allExamsForCourse" => array("href" => "exam/course/{course}"),
			"examResult" => array("href" => "exam/{id}/student/{student}"),
			"latestExamResults" => array("href" => "exam/latest"),
		)
	),
	
	array(
		"path" => "exam/{id}", 
		"description" => "Information about exam", 
		"method" => "GET", 
		"params" => [ 
			"withResults" => [ "type" => "bool", "default" => "false", "description" => "Also return exam results" ]
		],
		"code" => function($p) { 
			$exam = Exam::fromId($p['id']);
			if ($p['withResults'])
				$exam->results = ExamResult::fromExam($p['id']);
			return $exam;
		},
		"acl" => function($p) { 
			$exam = Exam::fromId($p['id']); // Reuse in code somehow?
			return AccessControl::teacherLevel($exam->CourseUnit->id, $exam->AcademicYear->id); 
		},
		"hateoas_links" => array(
			"exam" => array("href" => "exam/{id}"),
			"allExamsForCourse" => array("href" => "exam/course/{course}"),
			"examResult" => array("href" => "exam/{id}/student/{student}"),
			"latestExamResults" => array("href" => "exam/latest"),
		)
	),
	
	array(
		"path" => "exam/course/{course}", 
		"description" => "List of exams on course", 
		"method" => "GET", 
		"code" => function($p) { 
			return Exam::fromCourseAndYear($p['course']);
		},
		"acl" => function($p) { 
			return AccessControl::teacherLevel($p['course'], 0 /* current year */); 
		},
		"hateoas_links" => array(
			"exam" => array("href" => "exam/{id}"),
			"allExamsForCourse" => array("href" => "exam/course/{course}"),
			"examResult" => array("href" => "exam/{id}/student/{student}"),
			"latestExamResults" => array("href" => "exam/latest"),
		)
	),
	
	array(
		"path" => "exam/course/{course}/{year}", 
		"description" => "Information about exam", 
		"method" => "GET", 
		"code" => function($p) { 
			return Exam::fromCourseAndYear($p['course'], $p['year']);
		},
		"acl" => function($p) { 
			return AccessControl::teacherLevel($p['course'], $p['year']); 
		},
		"hateoas_links" => array(
			"exam" => array("href" => "exam/{id}"),
			"allExamsForCourse" => array("href" => "exam/course/{course}"),
			"examResult" => array("href" => "exam/{id}/student/{student}"),
			"latestExamResults" => array("href" => "exam/latest"),
		)
	),
	
	array(
		"path" => "exam/{id}/student/{student}", 
		"description" => "Information about exam result achieved by student", 
		"method" => "GET", 
		"code" => function($p) { 
			return ExamResult::fromStudentAndExam($p['student'], $p['id']);
		},
		"acl" => function($p) { 
			$exam = Exam::fromId($p['id']);
			return AccessControl::teacherLevel($exam->CourseUnit->id, $exam->AcademicYear->id); 
		},
		"hateoas_links" => array(
			"exam" => array("href" => "exam/{id}"),
			"allExamsForCourse" => array("href" => "exam/course/{course}"),
			"examResult" => array("href" => "exam/{id}/student/{student}"),
			"latestExamResults" => array("href" => "exam/latest"),
		)
	),
	
	array(
		"path" => "exam/{id}/student/{student}", 
		"description" => "Update exam result for student", 
		"method" => "PUT", 
		"params" => [ 
			"examResult" => [ "type" => "object", "class" => "ExamResult" ]
		],
		"code" => function($p) { 
			$er = ExamResult::fromStudentAndExam($p['student'], $p['id']);
			$er->setExamResult($p['examResult']->result);
			return $er;
		},
		"acl" => function($p) { 
			$exam = Exam::fromId($p['id']);
			return AccessControl::teacherLevel($exam->CourseUnit->id, $exam->AcademicYear->id); 
		},
		"hateoas_links" => array(
			"exam" => array("href" => "exam/{id}"),
			"allExamsForCourse" => array("href" => "exam/course/{course}"),
			"examResult" => array("href" => "exam/{id}/student/{student}"),
			"latestExamResults" => array("href" => "exam/latest"),
		)
	),
	
	array(
		"path" => "exam/{id}/student/{student}", 
		"description" => "Add new exam result for student",  // This is actually the same as PUT
		"method" => "POST", 
		"params" => [ 
			"examResult" => [ "type" => "object", "class" => "ExamResult" ]
		],
		"code" => function($p) { 
			$er = ExamResult::fromStudentAndExam($p['student'], $p['id']);
			$er->setExamResult($p['examResult']->result);
			return $er;
		},
		"acl" => function($p) { 
			$exam = Exam::fromId($p['id']);
			return AccessControl::teacherLevel($exam->CourseUnit->id, $exam->AcademicYear->id); 
		},
		"hateoas_links" => array(
			"exam" => array("href" => "exam/{id}"),
			"allExamsForCourse" => array("href" => "exam/course/{course}"),
			"examResult" => array("href" => "exam/{id}/student/{student}"),
			"latestExamResults" => array("href" => "exam/latest"),
		)
	),
	
	array(
		"path" => "exam/{id}/student/{student}", 
		"description" => "Delete exam result for student", 
		"method" => "DELETE", 
		"params" => [ 
			"examResult" => [ "type" => "object", "class" => "ExamResult" ]
		],
		"code" => function($p) { 
			$er = ExamResult::fromStudentAndExam($p['student'], $p['id']);
			$er->deleteExamResult();
			return $er;
		},
		"acl" => function($p) { 
			$exam = Exam::fromId($p['id']);
			return AccessControl::teacherLevel($exam->CourseUnit->id, $exam->AcademicYear->id); 
		},
		"hateoas_links" => array(
			"exam" => array("href" => "exam/{id}"),
			"allExamsForCourse" => array("href" => "exam/course/{course}"),
			"examResult" => array("href" => "exam/{id}/student/{student}"),
			"latestExamResults" => array("href" => "exam/latest"),
		)
	),
	
	array(
		"path" => "exam/latest/{student}", 
		"description" => "Latest exam results for student", 
		"params" => [ 
			"count" => [ "type" => "int", "default" => "10", "description" => "Number of latest exam results for student (no more than 100 results will be returned, for performance reasons)" ]
		],
		"method" => "GET", 
		"code" => function($p) { 
			if ($p['count'] > 100) 
				$p['count'] = 100;
			return ExamResult::getLatestForStudent(Session::$userid, $p['count']);
		},
		"acl" => function($p) { 
			return AccessControl::self($p['student']) || AccessControl::privilege('studentska'); 
		},
		"hateoas_links" => array(
			"exam" => array("href" => "exam/{id}"),
			"allExamsForCourse" => array("href" => "exam/course/{course}"),
			"examResult" => array("href" => "exam/{id}/student/{student}"),
			"latestExamResults" => array("href" => "exam/latest"),
		)
	),
	
	
	
	// HOMEWORK
	
	array(
		"path" => "homework", 
		"description" => "List of hateoas links", 
		"method" => "GET", 
		"code" => function($p) { 
			return new stdClass;
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"homework" => array("href" => "homework/{id}"),
			"allHomeworksForCourse" => array("href" => "homework/course/{course}/{year}"),
			"homeworkAssignment" => array("href" => "homework/{id}/{asgn}/student/{student}"),
		)
	),
	
	array(
		"path" => "homework/{id}", 
		"description" => "Information about homework", 
		"method" => "GET", 
		"code" => function($p) { 
			return Homework::fromId($p['id']);
		},
		"acl" => function($p) { 
			$hw = Homework::fromId($p['id']); // Reuse in "code" somehow?
			return AccessControl::teacherLevel($hw->CourseUnit->id, $hw->AcademicYear->id);
		},
		"hateoas_links" => array(
			"homework" => array("href" => "homework/{id}"),
			"allHomeworksForCourse" => array("href" => "homework/course/{course}/{year}"),
			"homeworkAssignment" => array("href" => "homework/{id}/{asgn}/student/{student}"),
		)
	),
	
	array(
		"path" => "homework/course/{course}", 
		"description" => "List of homeworks on course", 
		"method" => "GET", 
		"code" => function($p) { 
			$cy = AcademicYear::getCurrent();
			return Homework::fromCourse($p['course'], $cy->id);
		},
		"acl" => function($p) { 
			return AccessControl::teacherLevel($p['course'], 0 /* current year */); 
		},
		"hateoas_links" => array(
			"homework" => array("href" => "homework/{id}"),
			"allHomeworksForCourse" => array("href" => "homework/course/{course}/{year}"),
			"homeworkAssignment" => array("href" => "homework/{id}/{asgn}/student/{student}"),
		)
	),
	
	array(
		"path" => "homework/course/{course}/{year}", 
		"description" => "List of homeworks on course", 
		"method" => "GET", 
		"code" => function($p) { 
			return Homework::fromCourse($p['course'], $p['year']);
		},
		"acl" => function($p) { 
			return AccessControl::teacherLevel($p['course'], $p['year']); 
		},
		"hateoas_links" => array(
			"homework" => array("href" => "homework/{id}"),
			"allHomeworksForCourse" => array("href" => "homework/course/{course}/{year}"),
			"homeworkAssignment" => array("href" => "homework/{id}/{asgn}/student/{student}"),
		)
	),
	
	array(
		"path" => "homework/course/{course}/student/{student}", 
		"description" => "Status of all homeworks on course for student", 
		"method" => "GET", 
		"params" => [ 
			"year" => [ "type" => "int", "default" => "0", "description" => "Academic year (if ommitted or 0, current year is used)" ],
			"scoringElement" => [ "type" => "int", "default" => "0", "description" => "Which activity of type 'homework' is requested (if ommitted or 0, all activities of type 'homework' will be returned)" ]
		],
		"code" => function($p) { 
			$asgn = Assignment::forStudentOnCourseUnit($p['student'], $p['course'], $p['year'], $p['scoringElement']);
			return $asgn;
		},
		"acl" => function($p) { 
			return AccessControl::self($p['id']) || AccessControl::privilege('studentska')
				|| AccessControl::teacherLevel($p['course'], $p['year']); 
		},
		"autoresolve" => array(),
		"hateoas_links" => array(
			"homework" => array("href" => "homework/{id}"),
			"allHomeworksForCourse" => array("href" => "homework/course/{course}/{year}"),
			"homeworkAssignment" => array("href" => "homework/{id}/{asgn}/student/{student}"),
		)
	),
	
	array(
		"path" => "homework/{id}/{asgn}/student/{student}", 
		"description" => "Status of submitted homework for student (with assignment number)", 
		"method" => "GET", 
		"code" => function($p) { 
			return Assignment::fromStudentHomeworkNumber($p['student'], $p['id'], $p['asgn']);
		},
		"acl" => function($p) { 
			$hw = Homework::fromId($p['id']);
			return AccessControl::self($p['student']) || AccessControl::teacherLevel($hw->CourseUnit->id, $hw->AcademicYear->id);
		}, // if student is not on course, there will be no assignments
		"hateoas_links" => array(
			"homework" => array("href" => "homework/{id}"),
			"allHomeworksForCourse" => array("href" => "homework/course/{course}/{year}"),
			"homeworkAssignment" => array("href" => "homework/{id}/{asgn}/student/{student}"),
			"homeworkFile" => array("href" => "homework/{id}/{asgn}/student/{student}/file"),
		)
	),
	
	array(
		"path" => "homework/{id}/{asgn}/student/{student}", 
		"description" => "Submit homework (for students)", 
		"method" => "POST", 
		"params" => [ 
			"homework" => [ "type" => "file" ]
		],
		"code" => function($p) { 
			$asgn = Assignment::fromStudentHomeworkNumber($p['student'], $p['id'], $p['asgn']);
			$asgn->submit($p['homework'], Session::$userid); 
			return $asgn;
		},
		"acl" => function($p) { 
			$hw = Homework::fromId($p['id']);
			return (AccessControl::self($p['student']) && AccessControl::isStudent($hw->CourseUnit->id, $hw->AcademicYear->id))
				|| AccessControl::teacherLevel($hw->CourseUnit->id, $hw->AcademicYear->id); // Teacher can submit homework on behalf of student
		},
		"hateoas_links" => array(
			"homework" => array("href" => "homework/{id}"),
			"allHomeworksForCourse" => array("href" => "homework/course/{course}/{year}"),
			"homeworkAssignment" => array("href" => "homework/{id}/{asgn}/student/{student}"),
		)
	),
	
	array(
		"path" => "homework/{id}/{asgn}/student/{student}", 
		"description" => "Change homework status (for teachers)", 
		"method" => "PUT", 
		"params" => [ 
			"assignment" => [ "type" => "object", "class" => "Assignment" ]
		],
		"code" => function($p) { 
			// TODO ensure that homework/assignment number are correct?
			$p['assignment']->author->id = Session::$userid;
			$p['assignment']->add();
			return $p['assignment'];
		},
		"acl" => function($p) { 
			$hw = Homework::fromId($p['id']);
			return AccessControl::teacherLevel($hw->CourseUnit->id, $hw->AcademicYear->id);
		},
		"hateoas_links" => array(
			"homework" => array("href" => "homework/{id}"),
			"allHomeworksForCourse" => array("href" => "homework/course/{course}/{year}"),
			"homeworkAssignment" => array("href" => "homework/{id}/{asgn}/student/{student}"),
		)
	),
	
	array(
		"path" => "homework/{id}/{asgn}/student/{student}/file", 
		"description" => "Get homework file for given assignment", 
		"method" => "GET",
		"encoding" => "none", // Skip JSON encoding result
		"code" => function($p) { 
			$asgn = Assignment::fromStudentHomeworkNumber($p['student'], $p['id'], $p['asgn']);
			$file = $asgn->getFile(); 
			readfile($file->fullPath()); 
			return ''; // Avoid warning that function returned nothing
		},
		"acl" => function($p) { 
			$hw = Homework::fromId($p['id']);
			return AccessControl::self($p['student']) || AccessControl::teacherLevel($hw->CourseUnit->id, $hw->AcademicYear->id);
		}, // if student is not on course, there will be no assignments
	),
	
	array(
		"path" => "homework/{id}/{asgn}/getAll", 
		"description" => "Get homework files of all students for given assignment", 
		"method" => "GET",
		"params" => [ 
			// TODO: type: enum
			"filenames" => [ "type" => "string", "default" => "file_id", "description" => "specifies how files inside ZIP will be named:\n- \"fullname\"  - Surname_Name_IdNumber\n- \"login\"     - students' login\n- \"person_id\" - integer representing internal unique ID of each student\n- \"file_id\"   - integer representing internal unique ID of submitted file" ]
		],
		"encoding" => "none", // Skip JSON encoding result
		"code" => function($p) { 
			header('Content-Type: application/zip');
			$zip = Assignment::getAllAssignments($p['id'], $p['asgn'], $p['filenames']); 
			readfile($zip->fullPath()); 
			return ''; // Avoid warning that function returned nothing
		},
		"acl" => function($p) { 
			$hw = Homework::fromId($p['id']);
			return AccessControl::teacherLevel($hw->CourseUnit->id, $hw->AcademicYear->id);
		},
	),
	
	array(
		"path" => "homework/{id}/{asgn}/autotest", 
		"description" => "Get .autotest file for assignment", 
		"method" => "GET",
		"code" => function($p) { 
			return AutotestFile::fromHomeworkNumber($p['id'], $p['asgn']);
		},
		"acl" => function($p) { 
			$hw = Homework::fromId($p['id']);
			return AccessControl::teacherLevel($hw->CourseUnit->id, $hw->AcademicYear->id);
		},
	),
	
	array(
		"path" => "homework/{id}/{asgn}/autotest", 
		"description" => "Update .autotest file for assignment", 
		"method" => "PUT",
		"params" => [ 
			"autotest" => [ "type" => "object", "class" => "AutotestFile" ]
		],
		"code" => function($p) { 
			$p['autotest']->update($p['id'], $p['asgn']);
			return $p['autotest'];
		},
		"acl" => function($p) { 
			$hw = Homework::fromId($p['id']);
			return AccessControl::teacherLevel($hw->CourseUnit->id, $hw->AcademicYear->id);
		},
	),
	
	
	
	// QUIZ
	
	array(
		"path" => "quiz", 
		"description" => "List of hateoas links", 
		"method" => "GET", 
		"code" => function($p) { 
			return new stdClass;
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
			"latestQuizzesForStudent" => array("href" => "quiz/latest/{student}"),
		)
	),
	
	array(
		"path" => "quiz/{id}", 
		"description" => "Information about quiz", 
		"method" => "GET", 
		"code" => function($p) { 
			return Quiz::fromId($p['id']);
		},
		"acl" => function($p) { 
			$quiz = Quiz::fromId($p['id']); // Reuse in "code" somehow?
			return AccessControl::teacherLevel($quiz->CourseUnit->id, $quiz->AcademicYear->id);
		},
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
			"latestQuizzesForStudent" => array("href" => "quiz/latest/{student}"),
		)
	),
	
	array(
		"path" => "quiz/{id}/take", 
		"description" => "Get quiz questions with offered answers", 
		"method" => "GET", 
		"code" => function($p) { 
			return Quiz::take(Session::$userid, $p['id']);
		},
		"acl" => function($p) { 
			$quiz = Quiz::fromId($p['id']);
			return AccessControl::isStudent($quiz->CourseUnit->id, $quiz->AcademicYear->id);
		},
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizSubmit" => array("href" => "quiz/{id}/submit"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
			"latestQuizzesForStudent" => array("href" => "quiz/latest/{student}"),
		)
	),
	
	array(
		"path" => "quiz/{id}/submit", 
		"description" => "When student takes a quiz and completes all answers, they submit the Quiz object here", 
		"method" => "POST", 
		"params" => [ 
			"quiz" => [ "type" => "object", "class" => "Quiz" ]
		],
		"code" => function($p) { 
			return $p['quiz']->submit(Session::$userid);
		},
		"acl" => function($p) { 
			return AccessControl::isStudent($p['quiz']->CourseUnit->id, $p['quiz']->AcademicYear->id);
		},
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
			"latestQuizzesForStudent" => array("href" => "quiz/latest/{student}"),
		)
	),
	
	array(
		"path" => "quiz/{id}/student", 
		"description" => "Quiz results for student", 
		"method" => "GET", 
		"code" => function($p) { 
			return QuizResult::fromStudentAndQuiz(Session::$userid, $p['id']);
		},
		"acl" => function($p) { 
			$quiz = Quiz::fromId($p['id']);
			return AccessControl::isStudent($quiz->CourseUnit->id, $quiz->AcademicYear->id);
		},
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
			"latestQuizzesForStudent" => array("href" => "quiz/latest/{student}"),
		)
	),
	
	array(
		"path" => "quiz/{id}/student/{student}", 
		"description" => "Quiz results for student", 
		"method" => "GET", 
		"code" => function($p) { 
			return QuizResult::fromStudentAndQuiz($p['student'], $p['id']);
		},
		"acl" => function($p) { 
			$quiz = Quiz::fromId($p['id']);
			return AccessControl::teacherLevel($quiz->CourseUnit->id, $quiz->AcademicYear->id);
		},
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
			"latestQuizzesForStudent" => array("href" => "quiz/latest/{student}"),
		)
	),
	
	array(
		"path" => "quiz/{id}/student/{student}", 
		"description" => "Delete result (reset quiz) for student", 
		"method" => "DELETE", 
		"code" => function($p) { 
			$qr = QuizResult::fromStudentAndQuiz($p['student'], $p['id']);
			$qr->delete();
		},
		"acl" => function($p) { 
			$quiz = Quiz::fromId($p['id']);
			return AccessControl::teacherLevel($quiz->CourseUnit->id, $quiz->AcademicYear->id);
		},
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
			"latestQuizzesForStudent" => array("href" => "quiz/latest/{student}"),
		)
	),
	
	array(
		"path" => "quiz/course/{course}", 
		"description" => "List of quizzes for course", 
		"method" => "GET", 
		"code" => function($p) { 
			return Quiz::fromCourse($p['course']);
		},
		"acl" => function($p) { 
			return AccessControl::teacherLevel($p['course'], 0 /* current year */); 
		},
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
			"latestQuizzesForStudent" => array("href" => "quiz/latest/{student}"),
		)
	),
	
	array(
		"path" => "quiz/course/{course}/{year}", 
		"description" => "List of quizzes for course", 
		"method" => "GET", 
		"code" => function($p) { 
			return Quiz::fromCourse($p['course'], $p['year']);
		},
		"acl" => function($p) { 
			return AccessControl::teacherLevel($p['course'], $p['year']); 
		},
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
			"latestQuizzesForStudent" => array("href" => "quiz/latest/{student}"),
		)
	),
	
	array(
		"path" => "quiz/latest/{student}", 
		"description" => "Currently open quizzes for student", 
		"method" => "GET", 
		"code" => function($p) { 
			return Quiz::getLatestForStudent($p['student']);
		},
		"acl" => function($p) { 
			return AccessControl::self($p['student']); 
		},
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
			"latestQuizzesForStudent" => array("href" => "quiz/latest/{student}"),
		)
	),
	
	
	
	// EVENT
	
	array(
		"path" => "event", 
		"description" => "List of hateoas links", 
		"method" => "GET", 
		"code" => function($p) { 
			return new stdClass;
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"event" => array("href" => "event/{id}"),
			"eventRegister" => array("href" => "event/{id}/register/{student}"),
			"eventsUpcoming" => array("href" => "event/upcoming/{student}"),
			"eventsRegistered" => array("href" => "event/registered/{student}"),
		)
	),
	
	array(
		"path" => "event/{id}", 
		"description" => "Information about event", 
		"method" => "GET", 
		"code" => function($p) { 
			return Event::fromId($p['id']);
		},
		"acl" => function($p) { 
			$evt = Event::fromId($p['id']); // Reuse in "code" somehow?
			return AccessControl::teacherLevel($evt->CourseUnit->id, $evt->AcademicYear->id) 
				|| AccessControl::isStudent($evt->CourseUnit->id, $evt->AcademicYear->id);
		},
		"hateoas_links" => array(
			"event" => array("href" => "event/{id}"),
			"eventRegister" => array("href" => "event/{id}/register/{student}"),
			"eventsUpcoming" => array("href" => "event/upcoming/{student}"),
			"eventsRegistered" => array("href" => "event/registered/{student}"),
		)
	),
	
	array(
		"path" => "event/{id}/register/{student}", 
		"description" => "Register for event", 
		"method" => "POST", 
		"code" => function($p) { 
			$evt = Event::fromId($p['id']);
			if ($p['student'] == Session::$userid)
				$evt->register($p['student']); 
			else /* teacher */ 
				$evt->register($p['student'], true, false); 
			return $evt;
		},
		"acl" => function($p) { 
			$evt = Event::fromId($p['id']); // Reuse in "code" somehow?
			return AccessControl::teacherLevel($evt->CourseUnit->id, $evt->AcademicYear->id) 
				|| AccessControl::self($p['student']);
		},
		"hateoas_links" => array(
			"event" => array("href" => "event/{id}"),
			"eventRegister" => array("href" => "event/{id}/register/{student}"),
			"eventsUpcoming" => array("href" => "event/upcoming/{student}"),
			"eventsRegistered" => array("href" => "event/registered/{student}"),
		)
	),
	
	array(
		"path" => "event/{id}/register/{student}", 
		"description" => "Unregister from event", 
		"method" => "DELETE", 
		"code" => function($p) { 
			$evt = Event::fromId($p['id']);
			$evt->unregister($p['student']);
			return $evt;
		},
		"acl" => function($p) { 
			$evt = Event::fromId($p['id']); // Reuse in "code" somehow?
			return AccessControl::teacherLevel($evt->CourseUnit->id, $evt->AcademicYear->id) 
				|| AccessControl::self($p['student']);
		},
		"hateoas_links" => array(
			"event" => array("href" => "event/{id}"),
			"eventRegister" => array("href" => "event/{id}/register/{student}"),
			"eventsUpcoming" => array("href" => "event/upcoming/{student}"),
			"eventsRegistered" => array("href" => "event/registered/{student}"),
		)
	),
	
	array(
		"path" => "event/upcoming/{student}", 
		"description" => "List upcoming events for student", 
		"method" => "GET", 
		"code" => function($p) { 
			return Event::upcomingForStudent($p['student']);
		},
		"acl" => function($p) { 
			return AccessControl::self($p['student']); 
		},
		"hateoas_links" => array(
			"event" => array("href" => "event/{id}"),
			"eventRegister" => array("href" => "event/{id}/register/{student}"),
			"eventsUpcoming" => array("href" => "event/upcoming/{student}"),
			"eventsRegistered" => array("href" => "event/registered/{student}"),
		)
	),
	
	array(
		"path" => "event/registered/{student}", 
		"description" => "List events that student is already registered for", 
		"method" => "GET", 
		"code" => function($p) { 
			return Event::registeredForStudent($p['student']);
		},
		"acl" => function($p) { 
			return AccessControl::self($p['student']); 
		},
		"hateoas_links" => array(
			"event" => array("href" => "event/{id}"),
			"eventRegister" => array("href" => "event/{id}/register/{student}"),
			"eventsUpcoming" => array("href" => "event/upcoming/{student}"),
			"eventsRegistered" => array("href" => "event/registered/{student}"),
		)
	),
	
	
	
	// CERTIFICATE
	
	array(
		"path" => "certificate", 
		"description" => "List of hateoas links", 
		"method" => "GET", 
		"code" => function($p) { 
			return new stdClass;
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"certificate" => array("href" => "certificate/{id}"),
			"certificatesForStudent" => array("href" => "certificate/student/{student}"),
			"certificatePurposesTypes" => array("href" => "certificate/purposesTypes"),
		)
	),
	
	array(
		"path" => "certificate/{id}", 
		"description" => "Information about particular certificate request", 
		"method" => "GET", 
		"code" => function($p) { 
			return Certificate::fromId($p['id']);
		},
		"acl" => function($p) {
			$cert = Certificate::fromId($p['id']); // Reuse in "code" somehow?
			return AccessControl::self($cert->student->id) || AccessControl::privilege('studentska');
		},
		"hateoas_links" => array(
			"certificate" => array("href" => "certificate/{id}"),
			"certificatesForStudent" => array("href" => "certificate/student/{student}"),
			"certificatePurposesTypes" => array("href" => "certificate/purposesTypes"),
		)
	),
	
	array(
		"path" => "certificate/{id}", 
		"description" => "Update certificate status", 
		"method" => "PUT", 
		"params" => [ 
			"certificate" => [ "type" => "object", "class" => "Certificate" ]
		],
		"code" => function($p) { 
			$result = new stdClass;
			$result->success = $p['certificate']->setStatus($p['certificate']->status);
			return $result;
		},
		"acl" => function($p) {
			return AccessControl::privilege('studentska');
		},
		"hateoas_links" => array(
			"certificate" => array("href" => "certificate/{id}"),
			"certificatesForStudent" => array("href" => "certificate/student/{student}"),
			"certificatePurposesTypes" => array("href" => "certificate/purposesTypes"),
		)
	),
	
	array(
		"path" => "certificate/{id}", 
		"description" => "Cancel certificate request", 
		"method" => "DELETE", 
		"code" => function($p) { 
			return Certificate::fromId($p['id'])->cancel();
		},
		"acl" => function($p) {
			$cert = Certificate::fromId($p['id']); // Reuse in "code" somehow?
			return AccessControl::self($cert->student->id) || AccessControl::privilege('studentska');
		},
		"hateoas_links" => array(
			"certificate" => array("href" => "certificate/{id}"),
			"certificatesForStudent" => array("href" => "certificate/student/{student}"),
			"certificatePurposesTypes" => array("href" => "certificate/purposesTypes"),
		)
	),
	
	array(
		"path" => "certificate/student/{student}", 
		"description" => "List of certificate requests for student", 
		"method" => "GET", 
		"code" => function($p) { 
			return Certificate::forStudent($p['student']);
		},
		"acl" => function($p) { 
			return AccessControl::self($p['student']); 
		},
		"hateoas_links" => array(
			"certificate" => array("href" => "certificate/{id}"),
			"certificatesForStudent" => array("href" => "certificate/student/{student}"),
			"certificatePurposesTypes" => array("href" => "certificate/purposesTypes"),
		)
	),
	
	array(
		"path" => "certificate/student/{student}", 
		"description" => "Request new certificate", 
		"method" => "POST", 
		"params" => [ 
			"certificate" => [ "type" => "object", "class" => "Certificate" ]
		],
		"code" => function($p) { 
			$cert = $p['certificate'];
			return Certificate::request($p['student'], intval($cert->CertificatePurpose), intval($cert->CertificateType));
		},
		"acl" => function($p) { 
			return AccessControl::self($p['student']); 
		},
		"hateoas_links" => array(
			"certificate" => array("href" => "certificate/{id}"),
			"certificatesForStudent" => array("href" => "certificate/student/{student}"),
			"certificatePurposesTypes" => array("href" => "certificate/purposesTypes"),
		)
	),

	array(
		"path" => "certificate/purposesTypes", 
		"description" => "Codebook for certificate purposes and types", 
		"method" => "GET", 
		"code" => function($p) { 
			return Certificate::purposesTypes();
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"certificate" => array("href" => "certificate/{id}"),
			"certificatesForStudent" => array("href" => "certificate/student/{student}"),
			"certificatePurposesTypes" => array("href" => "certificate/purposesTypes"),
		)
	),
	
	
	
	// ENROLLMENT
	
	array(
		"path" => "enrollment", 
		"description" => "List of hateoas links", 
		"method" => "GET", 
		"code" => function($p) { 
			return new stdClass;
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"currentEnrollment" => array("href" => "enrollment/current/{student}"),
			"allEnrollments" => array("href" => "enrollment/all/{student}"),
		)
	),
	
	array(
		"path" => "enrollment/current/{student}", 
		"description" => "Information about programme/semester that student is currently enrolled in", 
		"method" => "GET", 
		"code" => function($p) { 
			return Enrollment::getCurrentForStudent($p['student']);
		},
		"acl" => function($p) { 
			return AccessControl::self($p['student']) || AccessControl::privilege('studentska'); 
		},
		"hateoas_links" => array(
			"currentEnrollment" => array("href" => "enrollment/current/{student}"),
			"allEnrollments" => array("href" => "enrollment/all/{student}"),
		)
	),
	
	array(
		"path" => "enrollment/all/{student}", 
		"description" => "Information about programme/semester that student is currently enrolled in", 
		"method" => "GET", 
		"code" => function($p) { 
			return Enrollment::getAllForStudent($p['student']);
		},
		"acl" => function($p) { 
			return AccessControl::self($p['student']) || AccessControl::privilege('studentska'); 
		},
		"hateoas_links" => array(
			"currentEnrollment" => array("href" => "enrollment/current/{student}"),
			"allEnrollments" => array("href" => "enrollment/all/{student}"),
		)
	),
	
	
	
	// CURRICULUM
	
	array(
		"path" => "curriculum", 
		"description" => "List of hateoas links", 
		"method" => "GET", 
		"code" => function($p) { 
			return new stdClass;
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"curriculum" => array("href" => "curriculum/{id}"),
		)
	),
	
	array(
		"path" => "curriculum/{id}", 
		"description" => "Information about curriculum with list of courses", 
		"method" => "GET", 
		"params" => [ 
			"getCourses" => [ "type" => "bool", "default" => "false", "description" => "Also return information about individual courses" ]
		],
		"code" => function($p) { 
			$cur = Curriculum::fromId($p['id']);
			if ($p['getCourses']) 
				$cur->courses = CurriculumCourse::forCurriculum($p['id']);
			return $cur;
		},
		"acl" => function($p) { return AccessControl::loggedIn(); },
		"hateoas_links" => array(
			"curriculum" => array("href" => "curriculum/{id}"),
		)
	),
);

$ws_aliases = array(
);


?>

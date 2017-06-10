<?php

// List of services with wiring code

$wiring = array(
	// PERSON
	
	array(
		"path" => "person", 
		"description" => "Current user", 
		"method" => "GET", 
		"code" => "\$p = Person::fromId(Session::\$userid); \$p->getTitles(); return \$p;", 
		"acl" => "loggedIn()",
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
		"code" => "\$p = Person::fromId(\$id); \$p->getTitles(); return \$p;", 
		"acl" => "loggedIn()",
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
		"params" => array( "login" => "string" ),
		"code" => "\$p = Person::fromLogin(\$login); \$p->getTitles(); return \$p;", 
		"acl" => "loggedIn()",
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
		"params" => array( "query" => "string" ),
		"code" => "return Person::search(\$query);", 
		"autoresolve" => array(),
		"acl" => "loggedIn()",
		"hateoas_links" => array(
			"currentUser" => array("href" => "person"),
			"personByLogin" => array("href" => "person/byLogin?login={login}"),
			"personById" => array("href" => "person/{id}")
		)
	),
	
	array(
		"path" => "extendedPerson/{id}", 
		"description" => "Find user by id", 
		"method" => "GET", 
		"code" => "\$p = ExtendedPerson::fromId(\$id); return \$p;", 
		"autoresolve" => array(),
		"acl" => "self(\$id) || privilege('studentska') || privilege('siteadmin')"
	),
	
	
	
	// COURSE
	
	array(
		"path" => "course", 
		"description" => "List of hateoas links", 
		"method" => "GET", 
		"code" => "return new stdClass;", 
		"acl" => "all()",
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/?programme={programme}&semester={semester}'),
			'coursesForStudent' => array('href' => 'course/student/?student={student}'),
			'coursesForTeacher' => array('href' => 'course/teacher')
		)
	),
	
	array(
		"path" => "course/{id}", 
		"description" => "Course", 
		"method" => "GET", 
		"code" => "\$cy = AcademicYear::getCurrent(); return CourseUnitYear::fromCourseAndYear(\$id, \$cy->id);", 
		"acl" => "all()",
		"autoresolve" => array("CourseUnit", "AcademicYear", "Institution", "Scoring", "Programme", "ProgrammeType"),
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/?programme={programme}&semester={semester}'),
			'coursesForStudent' => array('href' => 'course/student/?student={student}'),
			'coursesForTeacher' => array('href' => 'course/teacher')
		)
	),
	
	array(
		"path" => "course/{id}/{year}", 
		"description" => "Course", 
		"method" => "GET", 
		"code" => "return CourseUnitYear::fromCourseAndYear(\$id, \$year);", 
		"acl" => "all()",
		"autoresolve" => array("CourseUnit", "AcademicYear", "Institution", "Scoring", "Programme"),
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/?programme={programme}&semester={semester}'),
			'coursesForStudent' => array('href' => 'course/student/?student={student}'),
			'coursesForTeacher' => array('href' => 'course/teacher')
		)
	),
	
	array(
		"path" => "course/teacher", 
		"description" => "List of courses for teacher", 
		"method" => "GET", 
		"code" => "return CourseUnitYear::forTeacher(Session::\$userid);", 
		"acl" => "privilege('nastavnik')",
		"autoresolve" => array("AcademicYear", "Institution", "Scoring"),
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/?programme={programme}&semester={semester}'),
			'coursesForStudent' => array('href' => 'course/student/?student={student}'),
			'coursesForTeacher' => array('href' => 'course/teacher')
		)
	),

	array(
		"path" => "course/teacher/{teacher}", 
		"description" => "List of courses for teacher", 
		"method" => "GET", 
		"code" => "return CourseUnitYear::forTeacher(\$teacher);", 
		"acl" => "self(\$teacher) || privilege('studentska')",
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/?programme={programme}&semester={semester}'),
			'coursesForStudent' => array('href' => 'course/student/?student={student}'),
			'coursesForTeacher' => array('href' => 'course/teacher')
		)
	),
	
	array(
		"path" => "course/student", 
		"description" => "List current courses for student", 
		"method" => "GET", 
		"params" => array( "student" => "int", "year" => "int" ),
		"code" => "if (\$student == 0) \$student=Session::\$userid; return Portfolio::getCurrentForStudent(\$student);", 
		"acl" => "privilege('student') && \$student==0 || self(\$student) || privilege('studentska')",
		"autoresolve" => array("CourseOffering", "AcademicYear", "CourseUnit", "Programme"),
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/?programme={programme}&semester={semester}'),
			'coursesForStudent' => array('href' => 'course/student/?student={student}'),
			'coursesForTeacher' => array('href' => 'course/teacher')
		)
	),
	
	array(
		"path" => "course/student/{course}", 
		"description" => "Details of specific course for student", 
		"method" => "GET", 
		"params" => array( "student" => "int", "year" => "int" ),
		"code" => "if (\$student == 0) \$student=Session::\$userid; \$p = Portfolio::fromCourseUnit(\$student, \$course, \$year); \$p->getScore(); \$p->getGrade(); return \$p;", 
		"acl" => "privilege('student') && \$student==0 || self(\$student) || privilege('studentska') || teacherLevel(\$course, \$year)",
		"autoresolve" => array("AcademicYear", "CourseUnit", "Programme"),
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/?programme={programme}&semester={semester}'),
			'coursesForStudent' => array('href' => 'course/student/?student={student}'),
			'coursesForTeacher' => array('href' => 'course/teacher')
		)
	),
);

$ws_aliases = array(
);


?>

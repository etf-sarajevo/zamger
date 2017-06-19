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
		"autoresolve" => array("AcademicYear", "Institution"),
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
		"path" => "course/{course}/student", 
		"description" => "Details of specific course for student", 
		"method" => "GET", 
		"params" => array( "student" => "int", "year" => "int" ),
		"code" => "if (\$student == 0) \$student=Session::\$userid; \$p = Portfolio::fromCourseUnit(\$student, \$course, \$year); \$p->getScore(); \$p->getGrade(); return \$p;", 
		"acl" => "privilege('student') && \$student==0 || self(\$student) || privilege('studentska') || teacherLevel(\$course, \$year)",
		"autoresolve" => array(),
		"hateoas_links" => array(
			"course" => array("href" => "course/{course}/{year}"),
			'coursesOnProgramme' => array('href' => 'course/?programme={programme}&semester={semester}'),
			'coursesForStudent' => array('href' => 'course/student/?student={student}'),
			'coursesForTeacher' => array('href' => 'course/teacher')
		)
	),
	
	
	
	// GROUP
	
	array(
		"path" => "group", 
		"description" => "List of hateoas links", 
		"method" => "GET", 
		"code" => "return new stdClass;", 
		"acl" => "loggedIn()",
		"hateoas_links" => array(
			"group" => array("href" => "group/{id}"),
			"allGroups" => array("href" => "group/course/{course}/?year={year}"),
			"allStudents" => array("href" => "group/course/{course}/allStudents/?year={year}"),
			"forStudent" => array("href" => "group/course/{course}/student/?student={student}&year={year}"),
		)
	),
	
	array(
		"path" => "group/{id}", 
		"description" => "Get group with id", 
		"method" => "GET", 
		"params" => array( "details" => "bool" ),
		"code" => "return Group::fromId(\$id, \$details);", 
		"acl" => "teacherLevelGroup(\$id)",
		"autoresolve" => array(),
		"hateoas_links" => array(
			"group" => array("href" => "group/{id}"),
			"allGroups" => array("href" => "group/course/{course}/?year={year}"),
			"allStudents" => array("href" => "group/course/{course}/allStudents/?year={year}"),
			"forStudent" => array("href" => "group/course/{course}/student/?student={student}&year={year}"),
		)
	),
	
	array(
		"path" => "group/course/{course}", 
		"description" => "Get list of groups with students on course", 
		"method" => "GET", 
		"params" => array( "year" => "int", "includeVirtual" => "bool", "getMembers" => "bool" ),
		"code" => "return Group::forCourseAndYear(\$course, \$year, \$includeVirtual);", 
		"acl" => "teacherLevel(\$course, \$year)",
		"autoresolve" => array(),
		"hateoas_links" => array(
			"group" => array("href" => "group/{id}"),
			"allGroups" => array("href" => "group/course/{course}/?year={year}"),
			"allStudents" => array("href" => "group/course/{course}/allStudents/?year={year}"),
			"forStudent" => array("href" => "group/course/{course}/student/?student={student}&year={year}"),
		)
	),
	
	array(
		"path" => "group/course/{course}/allStudents", 
		"description" => "Get all students on course (virtual group)", 
		"method" => "GET", 
		"params" => array( "year" => "int" ),
		"code" => "return Group::virtualForCourse(\$course, \$year);", 
		"acl" => "teacherLevel(\$course, \$year)", // FIXME use teacherLevelGroup and id of virtual group
		"autoresolve" => array(),
		"hateoas_links" => array(
			"group" => array("href" => "group/{id}"),
			"allGroups" => array("href" => "group/course/{course}/?year={year}"),
			"allStudents" => array("href" => "group/course/{course}/allStudents/?year={year}"),
			"forStudent" => array("href" => "group/course/{course}/student/?student={student}&year={year}"),
		)
	),
	
	array(
		"path" => "group/course/{course}/student", 
		"description" => "Get the list of groups that a student belongs to for given course", 
		"method" => "GET", 
		"params" => array( "student" => "int", "year" => "int" ),
		"code" => "if (\$student == 0) \$student=Session::\$userid; return Group::fromStudentAndCourse(\$student, \$course, \$year);",
		"acl" => "privilege('student') && \$student==0 || self(\$student) || teacherLevel(\$course, \$year)",
		"autoresolve" => array(),
		"hateoas_links" => array(
			"group" => array("href" => "group/{id}"),
			"allGroups" => array("href" => "group/course/{course}/?year={year}"),
			"allStudents" => array("href" => "group/course/{course}/allStudents/?year={year}"),
			"forStudent" => array("href" => "group/course/{course}/student/?student={student}&year={year}"),
		)
	),
	
	
	
	// CLASS/ATTENDANCE
	
	array(
		"path" => "class", 
		"description" => "List of hateoas links", 
		"method" => "GET", 
		"code" => "return new stdClass;", 
		"acl" => "loggedIn()",
		"hateoas_links" => array(
			"class" => array("href" => "class/{id}"),
			"allClassesInGroup" => array("href" => "class/group/{group}"),
			"attendance" => array("href" => "class/{id}/student/{student}"),
		)
	),
	
	array(
		"path" => "class/{id}", 
		"description" => "Get class information", 
		"method" => "GET", 
		"code" => "return ZClass::fromId(\$id);", 
		"acl" => "teacherLevelGroup(ZClass::fromId(\$id)->Group->id)",
		"hateoas_links" => array(
			"class" => array("href" => "class/{id}"),
			"allClassesInGroup" => array("href" => "class/group/{group}"),
			"attendance" => array("href" => "class/{id}/student/{student}"),
		)
	),
	
	array(
		"path" => "class/group/{group}", 
		"description" => "List of classes in group", 
		"method" => "GET", 
		"code" => "return ZClass::fromGroup(\$group);", 
		"acl" => "teacherLevelGroup(\$group)",
		"hateoas_links" => array(
			"class" => array("href" => "class/{id}"),
			"allClassesInGroup" => array("href" => "class/group/{group}"),
			"attendance" => array("href" => "class/{id}/student/{student}"),
		)
	),
	
	array(
		"path" => "class/{id}/student/{student}", 
		"description" => "Get information on attendance of student", 
		"method" => "GET", 
		"code" => "\$att = Attendance::fromStudentAndClass(\$student, \$id); \$att->getPresence(); return \$att;", 
		"acl" => "teacherLevelGroup(ZClass::fromId(\$id)->Group->id)",
		"hateoas_links" => array(
			"class" => array("href" => "class/{id}"),
			"allClassesInGroup" => array("href" => "class/group/{group}"),
			"attendance" => array("href" => "class/{id}/student/{student}"),
		)
	),
	
	array(
		"path" => "class/{id}/student/{student}", 
		"description" => "Update attendance of student", 
		"method" => "POST", 
		"params" => array( "att" => "object" ),
		"code" => "\$att->student->id = \$student; \$att->ZClass->id = \$id; \$att->setPresence(\$att->present);", 
		"acl" => "teacherLevelGroup(ZClass::fromId(\$id)->Group->id)",
		"hateoas_links" => array(
			"class" => array("href" => "class/{id}"),
			"allClassesInGroup" => array("href" => "class/group/{group}"),
			"attendance" => array("href" => "class/{id}/student/{student}"),
		)
	),
	
	array(
		"path" => "class/{id}/student/{student}", 
		"description" => "Set attendance of student", 
		"method" => "PUT", 
		"params" => array( "att" => "object" ),
		"code" => "\$att->student->id = \$student; \$att->ZClass->id = \$id; \$att->setPresence(\$att->present);", 
		"acl" => "teacherLevelGroup(ZClass::fromId(\$id)->Group->id)",
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
		"code" => "return new stdClass;", 
		"acl" => "loggedIn()",
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
		"code" => "return Exam::fromId(\$id);", 
		"acl" => "teacherLevel(Exam::fromId(\$id)->CourseUnit->id, Exam::fromId(\$id)->AcademicYear->id)",
		"hateoas_links" => array(
			"exam" => array("href" => "exam/{id}"),
			"allExamsForCourse" => array("href" => "exam/course/{course}"),
			"examResult" => array("href" => "exam/{id}/student/{student}"),
			"latestExamResults" => array("href" => "exam/latest"),
		)
	),
	
	array(
		"path" => "exam/course/{course}", 
		"description" => "Information about exam", 
		"method" => "GET", 
		"code" => "return Exam::fromCourseAndYear(\$course);", 
		"acl" => "teacherLevel(\$course, 0)",
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
		"code" => "return Exam::fromCourseAndYear(\$course, \$year);", 
		"acl" => "teacherLevel(\$course, \$year)",
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
		"code" => "return ExamResult::fromStudentAndExam(\$student, \$id);", 
		"acl" => "teacherLevel(Exam::fromId(\$id)->CourseUnit->id, Exam::fromId(\$id)->AcademicYear->id)",
		"hateoas_links" => array(
			"exam" => array("href" => "exam/{id}"),
			"allExamsForCourse" => array("href" => "exam/course/{course}"),
			"examResult" => array("href" => "exam/{id}/student/{student}"),
			"latestExamResults" => array("href" => "exam/latest"),
		)
	),
	
	array(
		"path" => "exam/latest", 
		"description" => "Latest exam results for student", 
		"method" => "GET", 
		"code" => "return ExamResult::getLatestForStudent(Session::\$userid, 10);", 
		"acl" => "loggedIn()",
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
		"code" => "return new stdClass;", 
		"acl" => "loggedIn()",
		"hateoas_links" => array(
			"homework" => array("href" => "homework/{id}"),
			"allHomeworksForCourse" => array("href" => "homework/course/{course}/{year}"),
		)
	),
	
	array(
		"path" => "homework/{id}", 
		"description" => "Information about homework", 
		"method" => "GET", 
		"code" => "return Homework::fromId(\$id);", 
		"acl" => "teacherLevel(Homework::fromId(\$id)->CourseUnit->id, Homework::fromId(\$id)->AcademicYear->id)",
		"hateoas_links" => array(
			"homework" => array("href" => "homework/{id}"),
			"allHomeworksForCourse" => array("href" => "homework/course/{course}/{year}"),
		)
	),
	
	array(
		"path" => "homework/course/{course}", 
		"description" => "List of homeworks on course", 
		"method" => "GET", 
		"code" => "return Homework::fromCourse(\$course);", 
		"acl" => "teacherLevel(\$course, 0)",
		"hateoas_links" => array(
			"homework" => array("href" => "homework/{id}"),
			"allHomeworksForCourse" => array("href" => "homework/course/{course}/{year}"),
		)
	),
	
	array(
		"path" => "homework/course/{course}/{year}", 
		"description" => "List of homeworks on course", 
		"method" => "GET", 
		"code" => "return Homework::fromCourse(\$course, \$year);", 
		"acl" => "teacherLevel(\$course, \$year)",
		"hateoas_links" => array(
			"homework" => array("href" => "homework/{id}"),
			"allHomeworksForCourse" => array("href" => "homework/course/{course}/{year}"),
		)
	),
	
	array(
		"path" => "homework/{id}/{asgn}/student", 
		"description" => "Status of submitted homework for student (with assignment number)", 
		"method" => "GET", 
		"code" => "return Assignment::fromId(Session::\$userid, \$id, \$asgn);", 
		"acl" => "loggedIn()",
		"hateoas_links" => array(
			"homework" => array("href" => "homework/{id}"),
			"allHomeworksForCourse" => array("href" => "homework/course/{course}/{year}"),
		)
	),
	
	array(
		"path" => "homework/{id}/{asgn}/student/{student}", 
		"description" => "Status of submitted homework for student (with assignment number)", 
		"method" => "GET", 
		"code" => "return Assignment::fromId(\$student, \$id, \$asgn);", 
		"acl" => "teacherLevel(Homework::fromId(\$id)->CourseUnit->id, Homework::fromId(\$id)->AcademicYear->id)",
		"hateoas_links" => array(
			"homework" => array("href" => "homework/{id}"),
			"allHomeworksForCourse" => array("href" => "homework/course/{course}/{year}"),
		)
	),
	
	
	
	// HOMEWORK
	
	array(
		"path" => "quiz", 
		"description" => "List of hateoas links", 
		"method" => "GET", 
		"code" => "return new stdClass;", 
		"acl" => "loggedIn()",
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
		)
	),
	
	array(
		"path" => "quiz/{id}", 
		"description" => "Information about quiz", 
		"method" => "GET", 
		"code" => "return Quiz::fromId(\$id);", 
		"acl" => "teacherLevel(Quiz::fromId(\$id)->CourseUnit->id, Quiz::fromId(\$id)->AcademicYear->id)",
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
		)
	),
	
	array(
		"path" => "quiz/{id}/take", 
		"description" => "Get quiz questions with offered answers", 
		"method" => "GET", 
		"code" => "return Quiz::take(Session::\$userid, \$id);", 
		"acl" => "isStudent(Quiz::fromId(\$id)->CourseUnit->id, Quiz::fromId(\$id)->AcademicYear->id)",
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizSubmit" => array("href" => "quiz/{id}/submit"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
		)
	),
	
	array(
		"path" => "quiz/{id}/submit", 
		"description" => "When student takes a quiz and completes all answers, they submit the Quiz object here", 
		"method" => "POST", 
		"params" => array( "quiz" => "object" ),
		"code" => "return \$quiz->submit(Session::\$userid);", 
		"acl" => "isStudent(\$quiz->CourseUnit->id, \$quiz->AcademicYear->id)",
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
		)
	),
	
	array(
		"path" => "quiz/{id}/student", 
		"description" => "Quiz results for student", 
		"method" => "GET", 
		"code" => "return QuizResult::fromStudentAndQuiz(Session::\$userid, \$id);", 
		"acl" => "isStudent(Quiz::fromId(\$id)->CourseUnit->id, Quiz::fromId(\$id)->AcademicYear->id)",
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
		)
	),
	
	array(
		"path" => "quiz/{id}/student/{student}", 
		"description" => "Quiz results for student", 
		"method" => "GET", 
		"code" => "return QuizResult::fromStudentAndQuiz(\$student, \$id);", 
		"acl" => "teacherLevel(Quiz::fromId(\$id)->CourseUnit->id, Quiz::fromId(\$id)->AcademicYear->id)",
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
		)
	),
	
	array(
		"path" => "quiz/{id}/student/{student}", 
		"description" => "Delete result (reset quiz) for student", 
		"method" => "DELETE", 
		"code" => "\$qr = QuizResult::fromStudentAndQuiz(\$student, \$id); \$qr->delete();", 
		"acl" => "teacherLevel(Quiz::fromId(\$id)->CourseUnit->id, Quiz::fromId(\$id)->AcademicYear->id)",
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
		)
	),
	
	array(
		"path" => "quiz/course/{course}", 
		"description" => "List of quizzes for course", 
		"method" => "GET", 
		"code" => "return Quiz::fromCourse(\$course);", 
		"acl" => "teacherLevel(\$course, 0)",
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
		)
	),
	
	array(
		"path" => "quiz/course/{course}/{year}", 
		"description" => "List of quizzes for course", 
		"method" => "GET", 
		"code" => "return Quiz::fromCourse(\$course, \$year);", 
		"acl" => "teacherLevel(\$course, \$year)",
		"hateoas_links" => array(
			"quiz" => array("href" => "quiz/{id}"),
			"quizTake" => array("href" => "quiz/{id}/take"),
			"quizResults" => array("href" => "quiz/{id}/student"),
			"quizResultsStudent" => array("href" => "quiz/{id}/student/{student}"),
			"allQuizzesForCourse" => array("href" => "quiz/course/{course}/{year}"),
		)
	),
);

$ws_aliases = array(
);


?>

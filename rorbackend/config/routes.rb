Zamger::Application.routes.draw do
  get "/hrm/ensemble/Nomination/getLatestForPerson", :controller => "Hrm::Ensemble::Nomination", :action => "get_latest_for_person"
  get "/hrm/ensemble/Nomination/:id", :controller => "Hrm::Ensemble::Nomination", :action => "show"

  get "/hrm/ensemble/engagement/from_teacher_and_course", :controller => "Hrm::Ensemble::Engagement", :action => "from_teacher_and_course"
  get "/hrm/ensemble/engagement/get_teachers_on_course", :controller => "Hrm::Ensemble::Engagement", :action => "get_teachers_on_course"

  get "/sis/Announcement/getLatestForPerson", :controller => "Sis::Announcement", :action => "get_latest_for_person"
  get "/sis/Announcement/:id", :controller => "Sis::Announcement", :action => "show"

  get "/Lms/Project/ProjectParams/fromCourse", :controller => "Lms::Project::ProjectParams", :action => "from_course"

  get "/Lms/Project/Project/get_all_for_course", :controller => "Lms::Project::Project", :action => "get_all_for_course"
  get "/Lms/Project/Project/from_member_and_course", :controller => "Lms::Project::Project", :action => "from_member_and_course"
  get "/Lms/Project/Project/:id/is_member", :controller => "Lms::Project::Project", :action => "is_member"
  get "/Lms/Project/Project/:id/get_members", :controller => "Lms::Project::Project", :action => "get_members"
  put "/Lms/Project/Project/:id/add_member", :controller => "Lms::Project::Project", :action => "add_member"
  delete "/Lms/Project/Project/:id/delete_member", :controller => "Lms::Project::Project", :action => "delete_member"
  get "/Lms/Project/Project/:id", :controller => "Lms::Project::Project", :action => "show"
  

  get "/Lms/Forum/ForumTopic/:id/getCountReplies", :controller => "Lms::Forum::Forumtopic", :action => "get_count_replies"
  post "/Lms/Forum/ForumTopic/:id/viewed", :controller => "Lms::Forum::Forumtopic", :action => "viewed"
  get "/Lms/Forum/ForumTopic/:id/getAllPosts", :controller => "Lms::Forum::Forumtopic", :action => "get_all_posts"
  put "/Lms/Forum/ForumTopic/:id/addReply", :controller => "Lms::Forum::Forumtopic", :action => "add_reply"
  get "/Lms/Forum/ForumTopic/:id", :controller => "Lms::Forum::Forumtopic", :action => "show"

  get "/Lms/Forum/ForumPost/:id", :controller => "Lms::Forum::ForumPost", :action => "show"
  delete "/Lms/Forum/ForumPost/:id", :controller => "Lms::Forum::ForumPost", :action => "delete"
  post "/Lms/Forum/ForumPost/:id", :controller => "Lms::Forum::ForumPost", :action => "update"

  get "/Lms/Forum/Forum/:id/getAllTopics", :controller => "Lms::Forum::Forum", :action => "get_all_topics"
  get "/Lms/Forum/Forum/:id/getTopicsCount", :controller => "Lms::Forum::Forum", :action => "get_topics_count"
  get "/Lms/Forum/Forum/:id/getLatestPosts", :controller => "Lms::Forum::Forum", :action => "get_latest_posts"
  put "/Lms/Forum/Forum/:id/startNewTopic", :controller => "Lms::Forum::Forum", :action => "start_new_topic"

  get "/lms/poll/PollResult/fromHash", :controller => "Lms::Poll::PollResult", :action => "from_hash"
  get "/lms/poll/PollResult/fromStudentAndPoll", :controller => "Lms::Poll::PollResult", :action => "from_student_and_poll"
  put "/lms/poll/PollResult", :controller => "Lms::Poll::PollResult", :action => "create"
  post "/lms/poll/PollResult/:id", :controller => "Lms::Poll::PollResult", :action => "update"
  get "/lms/poll/PollResult/:id", :controller => "Lms::Poll::PollResult", :action => "show"

  get "/lms/poll/PollAnswer/forQuestion", :controller => "Lms::Poll::PollQuestion", :action => "for_question"

  get "/lms/poll/PollQuestion/getAllForPoll", :controller => "Lms::Poll::PollQuestion", :action => "get_all_for_poll"
  post "/lms/poll/PollQuestion/:id/setAnswerRank", :controller => "Lms::Poll::PollQuestion", :action => "set_answer_rank"
  post "/lms/poll/PollQuestion/:id/setAnswerEssay", :controller => "Lms::Poll::PollQuestion", :action => "set_answer_essay"
  post "/lms/poll/PollQuestion/:id/setAnswerChoice", :controller => "Lms::Poll::PollQuestion", :action => "set_answer_choice"
  get "/lms/poll/PollQuestion/:id", :controller => "Lms::Poll::PollQuestion", :action => "show"

  get "/lms/poll/Poll/getActiveForAllCourses", :controller => "Lms::Poll::Poll", :action => "getActiveForAllCourses"
  get "/lms/poll/Poll/getActiveForCourse", :controller => "Lms::Poll::Poll", :action => "get_active_for_course"
  get "/lms/poll/Poll/is_active_for_course", :controller => "Lms::Poll::Poll", :action => "is_active_for_course"
  get "/lms/poll/Poll/:id", :controller => "Lms::Poll::Poll", :action => "show"

  get "lms/moodle/MoodleItem/get_latest_for_course", :controller => "Lms::Moodle::MoodleItem", :action => "get_latest_for_course"

  get "/lms/moodle/MoodleId/getMoodleId", :controller => "Lms::Moodle::MoodleId", :action => "get_moodle_id"

  get "/lms/homework/programmingLanguage/:id", :controller => "Lms::Homework::ProgrammingLanguage", :action => "show"

  get "/lms/homework/Homework/getLatestForStudent", :controller => "Lms::Homework::Homework", :action => "get_latest_for_student"
  get "/lms/homework/Homework/getReviewedForStudent", :controller => "Lms::Homework::Homework", :action => "get_reviewed_for_student"
  get "/lms/homework/Homework/fromCourse", :controller => "Lms::Homework::Homework", :action => "from_course"
  post "/lms/homework/Homework/updateScoreForStudent", :controller => "Lms::Homework::Homework", :action => "update_score_for_student"
  get "/lms/homework/Homework/:id", :controller => "Lms::Homework::Homework", :action => "show"

  put "/lms/homework/Diff", :controller => "Lms::Homework::Diff", :action => "create"

  get "/lms/homework/Assignment/fromStudentHomeworkNumber", :controller => "Lms::Homework::Assignment", :action => "from_student_homework_number"
  put "/lms/homework/Assignment", :controller => "Lms::Homework::Assignment", :action => "create"
  get "/lms/homework/Assignment/:id", :controller => "Lms::Homework::Assignment", :action => "show"

  get "message/:id/forPerson", :controller => "Common::Pm:Message", :action => "for_person"
  post "message/send", :controller => "Common::Pm:Message", :action => "send"
  get "message/getLatestForPerson", :controller => "Common::Pm:Message", :action => "get_latest_for_person"
  get "message/getOutboxForPerson", :controller => "Common::Pm:Message", :action => "get_outbox_for_person"
  get "message/:id", :controller => "Common::Pm:Message", :action => "show"

  get "/lms/exam/ExamResult/fromStudentAndExam", :controller => "Lms::Exam::ExamResult", :action => "from_student_and_exam"
  post "/lms/exam/ExamResult/:id/setExamResult", :controller => "Lms::Exam::ExamResult", :action => "set_exam_result"
  delete "/lms/exam/ExamResult/:id/deleteExamResult", :controller => "Lms::Exam::ExamResult", :action => "delete_exam_result"
  post "/lms/exam/ExamResult/:id/updateScoring", :controller => "Lms::Exam::ExamResult", :action => "update_scoring"
  get "/lms/exam/ExamResult/getLatestForStudent", :controller => "Lms::Exam::ExamResult", :action => "get_latest_for_student"
  get "/lms/exam/ExamResult/:id", :controller => "Lms::Exam::ExamResult", :action => "show"
  
  get "/lms/exam/Exam/fromCourse", :controller => "Lms::Exam::Exam", :action => "from_course"
  get "/lms/exam/Exam/:id", :controller => "Lms::Exam::Exam", :action => "show"
  
  get "/lms/attendance/Class/fromGroupAndScoringElement", :controller => "Lms::Attendance::Class", :action => "from_group_and_scoring_element"
  get "/lms/attendance/Class/:id", :controller => "Lms::Attendance::Class", :action => "show"

  get "/lms/attendance/Group/fromStudentAndCourse", :controller => "Lms::Attendance::Group", :action => "from_student_and_course"
  get "/lms/attendance/Group/:id/isMember", :controller => "Lms::Attendance::Group", :action => "is_member"
  get "/lms/attendance/Group/:id/isTeacher", :controller => "Lms::Attendance::Group", :action => "is_teacher"
  get "/lms/attendance/Group/:id", :controller => "Lms::Attendance::Group", :action => "show"

  get "/lms/attendance/Attendance/fromStudentAndClass", :controller => "Lms::Attendance::Attendance", :action => "from_student_and_class"
  get "/lms/attendance/Attendance/:id/getPresence", :controller => "Lms::Attendance::Attendance", :action => "get_presence"
  post "/lms/attendance/Attendance/:id/setPresence", :controller => "Lms::Attendance::Attendance", :action => "get_presence"
  post "/lms/attendance/Attendance/:id/updateScore", :controller => "Lms::Attendance::Attendance", :action => "update_score"

  get "/core/ScoringElement/:id", :controller => "Core::ScoringElement", :action => "show"
  
  get "/core/Scoring/:id/getScoringElements", :controller => "Core::Scoring", :action => "get_scoring_elements"
  get "/core/Scoring/:id", :controller => "Core::Scoring", :action => "show"

  post "/core/RSS/:id/updateTimestamp", :controller => "Core::Rss", :action => "update_timestamp"
  get "/core/RSS/fromPersonId", :controller => "Core::Rss", :action => "from_person_id"
  get "/core/RSS/:id", :controller => "Core::Rss", :action => "show"

  get "/core/ProgrammeType/:id", :controller => "Core::ProgrammeType", :action => "show"
  
  get "/core/Programme/:id", :controller => "Core::Programme", :action => "show"
  
  get "/core/Portfolio/fromCourseOffering", :controller => "Core::Portfolio", :action => "from_course_offering"
  get "/core/Portfolio/fromCourseUnit", :controller => "Core::Portfolio", :action => "from_course_unit"
  get "/core/Portfolio/:id/getGrade", :controller => "Core::Portfolio", :action => "get_grade"
  post "/core/Portfolio/:id/setGrade", :controller => "Core::Portfolio", :action => "set_grade"
  get "/core/Portfolio/:id/deleteGrade", :controller => "Core::Portfolio", :action => "delete_grade"
  get "/core/Portfolio/:id/getScore", :controller => "Core::Portfolio", :action => "get_score"
  post "/core/Portfolio/:id/setScore", :controller => "Core::Portfolio", :action => "set_score"
  delete "/core/Portfolio/:id/deleteScore", :controller => "Core::Portfolio", :action => "delete_score"
  get "/core/Portfolio/:id/getTotalScore", :controller => "Core::Portfolio", :action => "get_total_score"
  get "/core/Portfolio/:id/getMaxScore", :controller => "Core::Portfolio", :action => "get_max_score"
  get "/core/Portfolio/getLatestGradesForStudent", :controller => "Core::Portfolio", :action => "get_latest_grades_for_student"
  get "/core/Portfolio/:id/getCurrentForStudent", :controller => "Core::Portfolio", :action => "get_current_for_student"
  get "/core/Portfolio/getAllForStudent", :controller => "Core::Portfolio", :action => "get_all_for_student"
  get "/core/Portfolio/:id", :controller => "Core::Portfolio", :action => "show"

  get "/core/Person/search", :controller => "Core::Person", :action => "search"
  get "/core/Person/:id", :controller => "Core::Person", :action => "show"

  get "/core/Enrollment/getCurrentForStudent", :controller => "Core::Enrollment", :action => "get_current_for_student"
  get "/core/Enrollment/getAllForStudent", :controller => "Core::Enrollment", :action => "get_all_for_student"

  get "/core/CourseOffering/getCoursesOffered", :controller => "Core::CourseOffering", :action => "get_courses_offered"
  get "/core/CourseOffering/:id", :controller => "Core::CourseOffering", :action => "show"
  
  get "/core/CourseUnitYear/fromCourseAndYear", :controller => "Core::CourseUnitYear", :action => "from_course_and_year"
  get "/core/CourseUnitYear/teacherAccessLevel", :controller => "Core::CourseUnitYear", :action => "teacher_access_level"
  get "/core/CourseUnitYear/:id", :controller => "Core::CourseUnitYear", :action => "show"
  
  get "/core/CourseUnit/:id", :controller => "Core::CourseUnit", :action => "show"
  
  get "/core/AcademicYear/getCurrent", :controller => "Core::AcademicYear", :action => "get_current"
  get "/core/AcademicYear/:id", :controller => "Core::AcademicYear", :action => "show"
  post "/core/AcademicYear/:id/setAsCurrent", :controller => "Core::AcademicYear", :action => "set_as_current"

  get "/core/Auth/login", :controller => "Core::Auth", :action => "login"
  get "/core/Auth/logout", :controller => "Core::Auth", :action => "logout"

  # The priority is based upon order of creation:
  # first created -> highest priority.

  # Sample of regular route:
  #   match 'products/:id' => 'catalog#view'
  # Keep in mind you can assign values other than :controller and :action

  # Sample of named route:
  #   match 'products/:id/purchase' => 'catalog#purchase', :as => :purchase
  # This route can be invoked with purchase_url(:id => product.id)

  # Sample resource route (maps HTTP verbs to controller actions automatically):
  #   resources :products

  # Sample resource route with options:
  #   resources :products do
  #     member do
  #       get 'short'
  #       post 'toggle'
  #     end
  #
  #     collection do
  #       get 'sold'
  #     end
  #   end

  # Sample resource route with sub-resources:
  #   resources :products do
  #     resources :comments, :sales
  #     resource :seller
  #   end

  # Sample resource route with more complex sub-resources
  #   resources :products do
  #     resources :comments
  #     resources :sales do
  #       get 'recent', :on => :collection
  #     end
  #   end

  # Sample resource route within a namespace:
  #   namespace :admin do
  #     # Directs /admin/products/* to Admin::ProductsController
  #     # (app/controllers/admin/products_controller.rb)
  #     resources :products
  #   end

  # You can have the root of your site routed with "root"
  # just remember to delete public/index.html.
  # root :to => 'welcome#index'

  # See how all your routes lay out with "rake routes"

  # This is a legacy wild controller route that's not recommended for RESTful applications.
  # Note: This route will make all actions in every controller accessible via GET requests.
  # match ':controller(/:action(/:id(.:format)))'
end

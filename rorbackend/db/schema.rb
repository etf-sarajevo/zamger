# This file is auto-generated from the current state of the database. Instead
# of editing this file, please use the migrations feature of Active Record to
# incrementally modify your database, and then regenerate this schema definition.
#
# Note that this schema.rb definition is the authoritative source for your
# database schema. If you need to create the application database on another
# system, you should be using db:schema:load, not running all the migrations
# from scratch. The latter is a flawed and unsustainable approach (the more migrations
# you'll amass, the slower it'll run and the greater likelihood for issues).
#
# It's strongly recommended to check this file into your version control system.

ActiveRecord::Schema.define(:version => 20110823004356) do

  create_table "common_pm_messages", :force => true do |t|
    t.integer "type"
    t.integer "scope"
    t.integer "to_id"
    t.integer "from_id"
    t.time    "time"
    t.integer "ref_id",  :default => 0
    t.text    "subject"
    t.text    "text"
  end

  create_table "core_academic_years", :force => true do |t|
    t.string  "name",    :limit => 20
    t.boolean "current"
  end

  add_index "core_academic_years", ["current"], :name => "index_core_academic_years_on_current"
  add_index "core_academic_years", ["name"], :name => "unique_index_core_academic_years_name", :unique => true

  create_table "core_access_levels", :force => true do |t|
    t.integer  "person_id"
    t.integer  "course_unit_id"
    t.integer  "academic_year_id"
    t.enum     "access_level",     :limit => [:teacher, :super_assistent, :assistent]
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "core_acls", :force => true do |t|
  end

  create_table "core_auths", :force => true do |t|
    t.string  "login",       :limit => 50
    t.string  "password",    :limit => 20
    t.boolean "admin"
    t.string  "external_id", :limit => 50
    t.boolean "active"
  end

  create_table "core_cantons", :force => true do |t|
    t.string "name",       :limit => 50
    t.string "short_name", :limit => 5
  end

  create_table "core_countries", :force => true do |t|
    t.string "name", :limit => 30
  end

  create_table "core_course_offerings", :force => true do |t|
    t.integer "course_unit_id"
    t.integer "academic_year_id"
    t.integer "programme_id"
    t.integer "semester"
    t.boolean "mandatory"
  end

  create_table "core_course_unit_type_scoring_elements", :force => true do |t|
    t.integer  "course_unit_type_id"
    t.integer  "scoring_element_id"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "core_course_unit_types", :force => true do |t|
    t.string "name", :limit => 50
  end

  create_table "core_course_unit_years", :force => true do |t|
    t.integer "course_unit_id"
    t.integer "academic_year_id"
    t.integer "course_unit_type_id"
  end

  add_index "core_course_unit_years", ["course_unit_id", "academic_year_id"], :name => "unique_index_course_unit_id_academic_year_id", :unique => true

  create_table "core_course_units", :force => true do |t|
    t.string  "code",                :limit => 20
    t.string  "name",                :limit => 100
    t.string  "short_name",          :limit => 10
    t.integer "institution_id",                     :default => 0
    t.integer "course_unit_type_id"
    t.float   "ects"
  end

  create_table "core_curriculums", :force => true do |t|
    t.integer "for_year"
    t.integer "programme_id"
    t.integer "semester"
    t.integer "course_unit_id"
    t.boolean "mandatory"
  end

  create_table "core_documents", :force => true do |t|
  end

  create_table "core_enrollment_types", :force => true do |t|
    t.string "name", :limit => 30
  end

  create_table "core_enrollments", :force => true do |t|
    t.integer "student_id"
    t.integer "programme_id"
    t.integer "semester"
    t.integer "academic_year_id"
    t.integer "enrollment_type_id"
    t.boolean "repeat",             :default => false
    t.integer "curriculum_id",      :default => 0
    t.integer "document_id",        :default => 0
  end

  add_index "core_enrollments", ["student_id", "programme_id", "semester", "academic_year_id"], :name => "unique_key_student_programme_semester_academic_year", :unique => true

  create_table "core_ethnicities", :force => true do |t|
    t.string "name", :limit => 50
  end

  create_table "core_final_grades", :force => true do |t|
    t.integer "student_id"
    t.integer "course_unit_id"
    t.integer "academic_year_id"
    t.integer "grade",            :limit => 3
    t.time    "date"
    t.integer "document_id"
  end

  create_table "core_institutions", :force => true do |t|
    t.string  "name",       :limit => 100
    t.integer "parent",                    :default => 0
    t.string  "short_name", :limit => 10
  end

  create_table "core_people", :force => true do |t|
    t.string  "name",                  :limit => 30
    t.string  "surname",               :limit => 30
    t.string  "fathers_name",          :limit => 30
    t.string  "fathers_surname",       :limit => 30
    t.string  "mothers_name",          :limit => 30
    t.string  "mothers_surname",       :limit => 30
    t.enum    "gender",                :limit => [:M, :Z]
    t.string  "email",                 :limit => 100
    t.string  "student_id_number",     :limit => 10
    t.date    "date_of_birth"
    t.integer "place_of_birth_id"
    t.integer "ethnicity_id"
    t.integer "nationality_id"
    t.boolean "soldier_category"
    t.string  "personal_id_number",    :limit => 14
    t.string  "address",               :limit => 50
    t.integer "address_place_id"
    t.string  "phone",                 :limit => 15
    t.integer "canton_id"
    t.boolean "for_delete",                                :default => false
    t.integer "professional_level_id"
    t.integer "science_level_id"
    t.string  "picture",               :limit => 50
  end

  create_table "core_places", :force => true do |t|
    t.string  "name",            :limit => 40
    t.integer "municipality_id"
    t.integer "country_id"
  end

  create_table "core_portfolios", :force => true do |t|
    t.integer "student_id"
    t.integer "course_offering_id"
  end

  add_index "core_portfolios", ["student_id", "course_offering_id"], :name => "index_core_portfolios_on_student_id_and_course_offering_id", :unique => true

  create_table "core_professional_levels", :force => true do |t|
    t.string "name",  :limit => 100
    t.string "title", :limit => 15
  end

  create_table "core_programme_types", :force => true do |t|
    t.string  "name"
    t.integer "cycle"
    t.integer "duration"
    t.boolean "accepts_students"
  end

  create_table "core_programmes", :force => true do |t|
    t.string  "name",             :limit => 100
    t.string  "short_name",       :limit => 10
    t.integer "final_semester",                  :default => 0
    t.integer "institution_id",                  :default => 0
    t.boolean "accepts_students"
    t.integer "type_id"
    t.integer "precondition"
  end

  create_table "core_rsses", :force => true do |t|
    t.integer "auth_id"
    t.time    "accessed_at"
  end

  create_table "core_science_levels", :force => true do |t|
    t.string "name",  :limit => 50
    t.string "title", :limit => 15
  end

  create_table "core_scoring_element_scores", :force => true do |t|
    t.integer  "student_id"
    t.integer  "course_offering_id"
    t.integer  "scoring_element_id"
    t.float    "score"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "core_scoring_elements", :force => true do |t|
    t.string  "name",           :limit => 40
    t.string  "gui_name",       :limit => 20
    t.string  "short_gui_name", :limit => 20
    t.integer "scoring_id"
    t.float   "max"
    t.float   "pass"
    t.string  "option",         :limit => 100
    t.boolean "mandatory",                     :default => false
  end

  create_table "core_scorings", :force => true do |t|
    t.string "name",                :limit => 20
    t.string "options_description", :limit => 100
  end

  create_table "hrm_ensemble_domains", :force => true do |t|
    t.integer "institution_id"
    t.string  "name",           :limit => 100
  end

  create_table "hrm_ensemble_engagement_statuses", :force => true do |t|
    t.string "name", :limit => 50
  end

  create_table "hrm_ensemble_engagements", :force => true do |t|
    t.integer "course_unit_id"
    t.integer "academic_year_id"
    t.integer "person_id"
    t.integer "engagement_status_id"
  end

  add_index "hrm_ensemble_engagements", ["person_id", "course_unit_id", "academic_year_id"], :name => "unique_index_person_id_course_unit_id_academic_year_id"

  create_table "hrm_ensemble_nominations", :force => true do |t|
    t.integer "person_id"
    t.integer "rank_id"
    t.date    "date_named"
    t.date    "date_expired"
    t.integer "domain_id"
    t.integer "subdomain_id"
    t.boolean "part_time"
    t.boolean "other_institution"
  end

  create_table "hrm_ensemble_ranks", :force => true do |t|
    t.string "name",  :limit => 50
    t.string "title", :limit => 10
  end

  create_table "hrm_ensemble_subdomains", :force => true do |t|
    t.integer  "domain_id"
    t.string   "name",       :limit => 100
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "lms_attendance_attendances", :force => true do |t|
    t.integer "student_id"
    t.integer "class_id"
    t.boolean "present",    :default => false
    t.boolean "plus_minus", :default => false
  end

  create_table "lms_attendance_classes", :force => true do |t|
    t.date    "date"
    t.time    "time"
    t.integer "teacher_id"
    t.integer "group_id"
    t.integer "scoring_element_id"
  end

  create_table "lms_attendance_groups", :force => true do |t|
    t.string  "name",             :limit => 100
    t.integer "course_unit_id"
    t.integer "academic_year_id"
    t.boolean "virtual"
  end

  create_table "lms_attendance_student_groups", :force => true do |t|
    t.integer "student_id"
    t.integer "group_id"
  end

  create_table "lms_attendance_students_groups", :force => true do |t|
    t.integer "student_id", :default => 0
    t.integer "group_id",   :default => 0
  end

  create_table "lms_attendance_teacher_groups", :force => true do |t|
    t.integer "teacher_id"
    t.integer "group_id"
  end

  create_table "lms_exam_exam_results", :force => true do |t|
    t.integer "student_id", :default => 0
    t.integer "exam_id",    :default => 0
    t.float   "result",     :default => 0.0
  end

  add_index "lms_exam_exam_results", ["student_id", "exam_id"], :name => "index_lms_exam_exam_results_on_student_id_and_exam_id", :unique => true

  create_table "lms_exam_exams", :force => true do |t|
    t.integer "course_unit_id",      :default => 0
    t.integer "academic_year_id",    :default => 0
    t.date    "date"
    t.time    "published_date_time"
    t.integer "scoring_element_id",  :default => 0
  end

  create_table "lms_forum_forum_post_texts", :force => true do |t|
    t.integer "forum_post_id"
    t.text    "text"
  end

  create_table "lms_forum_forum_posts", :force => true do |t|
    t.string  "subject",        :limit => 300
    t.time    "time"
    t.integer "author_id"
    t.integer "forum_topic_id"
  end

  create_table "lms_forum_forum_topics", :force => true do |t|
    t.time    "last_update"
    t.integer "first_post_id"
    t.integer "last_post_id"
    t.integer "views"
    t.integer "author_id"
    t.integer "forum_id"
  end

  create_table "lms_forum_forums", :force => true do |t|
    t.string  "name",             :limit => 200
    t.integer "course_unit_id"
    t.integer "academic_year_id"
    t.text    "description"
    t.text    "note"
    t.time    "time"
  end

  create_table "lms_homework_assignments", :force => true do |t|
    t.integer "homework_id",                   :default => 0
    t.integer "assign_no",                     :default => 0
    t.integer "student_id",                    :default => 0
    t.integer "status",                        :default => 0
    t.float   "score",                         :default => 0.0
    t.text    "compile_report"
    t.time    "time"
    t.text    "comment"
    t.string  "filename",       :limit => 200
    t.integer "author_id"
  end

  create_table "lms_homework_diffs", :force => true do |t|
    t.integer "assignment_id", :default => 0
    t.text    "diff"
  end

  create_table "lms_homework_homeworks", :force => true do |t|
    t.string  "name",                    :limit => 50
    t.integer "course_unit_id",                        :default => 0
    t.integer "academic_year_id",                      :default => 0
    t.integer "nr_assignments",                        :default => 0
    t.float   "score",                                 :default => 0.0
    t.time    "deadline"
    t.boolean "active",                                :default => false
    t.integer "programming_language_id",               :default => 0
    t.boolean "attachment",                            :default => false
    t.string  "allowed_extensions"
    t.string  "text"
    t.integer "scoring_element_id"
    t.time    "published_date_time"
  end

  create_table "lms_homework_programming_languages", :force => true do |t|
    t.string "name",      :limit => 50
    t.string "geshi",     :limit => 20
    t.string "extension", :limit => 10
  end

  create_table "lms_moodle_moodle_ids", :force => true do |t|
    t.integer "course_unit_id"
    t.integer "academic_year_id"
    t.integer "moodle_id"
  end

  create_table "lms_poll_poll_answer_essays", :force => true do |t|
    t.integer "poll_result_id"
    t.integer "poll_question_id"
    t.text    "answer"
  end

  create_table "lms_poll_poll_answer_ranks", :force => true do |t|
    t.integer "poll_result_id"
    t.integer "poll_question_id"
    t.integer "poll_question_choice_id"
  end

  create_table "lms_poll_poll_question_choices", :force => true do |t|
    t.integer "poll_question_id"
    t.text    "choice"
  end

  create_table "lms_poll_poll_question_types", :force => true do |t|
    t.string "type",          :limit => 32
    t.enum   "choice_exists", :limit => [:Y, :N]
    t.string "answers_table", :limit => 32
  end

  create_table "lms_poll_poll_questions", :force => true do |t|
    t.integer "poll_id",               :default => 0
    t.integer "poll_question_type_id"
    t.text    "text"
  end

  create_table "lms_poll_poll_results", :force => true do |t|
    t.integer "poll_id"
    t.time    "time"
    t.enum    "closed",           :limit => [:Y, :N]
    t.integer "course_unit_id"
    t.string  "unique_id",        :limit => 50
    t.integer "academic_year_id"
    t.integer "programme_id"
    t.integer "semester"
  end

  create_table "lms_poll_polls", :force => true do |t|
    t.time    "open_date"
    t.time    "close_date"
    t.string  "name"
    t.text    "description"
    t.boolean "active"
    t.boolean "editable"
    t.integer "academic_year_id"
  end

  create_table "lms_project_project_params", :force => true do |t|
    t.integer  "course_unit_id"
    t.integer  "academic_year_id"
    t.integer  "min_teams"
    t.integer  "max_teams"
    t.integer  "min_team_members"
    t.integer  "max_team_members"
    t.boolean  "locked",           :default => false
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "lms_project_project_students", :force => true do |t|
    t.integer  "student_id"
    t.integer  "project_id"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "lms_project_projects", :force => true do |t|
    t.string  "name",             :limit => 200
    t.integer "course_unit_id"
    t.integer "academic_year_id"
    t.text    "description"
    t.text    "note"
    t.time    "time"
  end

end

class Lms::Attendance::AttendanceController < ApplicationController
  caches_action :from_course_unit, :cache_path => Proc.new { |c| c.params }
  caches_action :from_student_and_class, :cache_path => Proc.new { |c| c.params }
  caches_action :get_score_from_course_unit, :cache_path => Proc.new { |c| c.params }
  # get "/lms/attendance/Attendance/fromStudentAndClass", :controller => "Lms::Attendance::Attendance", :action => "from_student_and_class"
  def from_student_and_class
    attendance = (Lms::Attendance::Attendance).from_student_and_class(params[:student_id], params[:class_id])
    respond_with_object(attendance)
  end
  
  # get "/lms/attendance/Attendance/:id/getPresence", :controller => "Lms::Attendance::Attendance", :action => "get_presence"
  def get_presence
    present = (Lms::Attendance::Attendance).get_presence(params[:id])
    respond_with_object(present)
  end
  
  # post "/lms/attendance/Attendance/:id/setPresence", :controller => "Lms::Attendance::Attendance", :action => "get_presence"
  def set_presence
      respond_save((Lms::Attendance::Attendance).set_presence(params[:id], params[:present]))
  end

  def update_score
  end
  
  
  
  # get "/lms/attendance/Attendance/fromCourseUnit", :controller => "Lms::Attendance::Attendance", :action => "from_course_unit"
  def from_course_unit
    attendances = (Lms::Attendance::Attendance).from_course_unit(params[:course_unit_id], params[:academic_year_id])
    respond_with_object(attendances)
  end
  
  # get "/lms/attendance/Attendance/getScoreFromCourseUnit", :controller => "Lms::Attendance::Attendance", :action => "get_score_from_course_unit"
  def get_score_from_course_unit
    score = (Core::ScoringElementScore).get_score_from_course_unit(params[:student_id], params[:course_unit_id], params[:academic_year_id], params[:attendance_id])
    respond_with_object(score)
  end
  
end

class Lms::Attendance::AttendanceController < ApplicationController
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
    respond_save((Lms::Attendance::Attendance).update_score(params[:student_id], params[:scoring_element_id], params[:course_unit_id], params[:academic_year_id]))
  end
  
end

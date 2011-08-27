class Lms::Attendance::GroupController < ApplicationController
  # get "/lms/attendance/Group/:id", :controller => "Lms::Attendance::Group", :action => "show"
  def show
    group = (Lms::Attendance::Group).find(params[:id])
    respond_with_object(group)
  end
  
  # get "/lms/attendance/Group/fromStudentAndCourse", :controller => "Lms::Attendance::Group", :action => "from_student_and_course"
  def from_student_and_course
    groups = (Lms::Attendance::Group).from_student_and_course(params[:student_id], params[:course_unit_id], params[:academic_year_id])
    respond_with_object(groups)
  end
  
  # get "/lms/attendance/Group/:id/isMember", :controller => "Lms::Attendance::Group", :action => "is_member"
  def is_member
    member = (Lms::Attendance::Group).is_member(params[:id], params[:student_id])
    respond_with_object(member)
  end
  
  # get "/lms/attendance/Group/:id/isTeacher", :controller => "Lms::Attendance::Group", :action => "is_teacher"
  def is_teacher
    teacher = Lms::Attendance::Group).is_teacher(parms[:id], params[:teacher_id])
    respond_with_object(teacher)
  end

end

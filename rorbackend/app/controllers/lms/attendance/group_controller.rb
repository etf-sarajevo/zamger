class Lms::Attendance::GroupController < ApplicationController
  caches_action :show, :cache_path => Proc.new { |c| c.params }
  caches_action :from_course_unit, :cache_path => Proc.new { |c| c.params }
  caches_action :from_course_unit_virtual, :cache_path => Proc.new { |c| c.params }
  caches_action :get_all_students, :cache_path => Proc.new { |c| c.params }
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
    teacher = (Lms::Attendance::Group).is_teacher(parms[:id], params[:teacher_id])
    respond_with_object(teacher)
  end
  
  # get "/lms/attendance/Group/fromCourseUnit", :controller => "Lms::Attendance::Group", :action => "from_course_unit"
  def from_course_unit
    groups = (Lms::Attendance::Group).from_course_unit(params[:course_unit_id], params[:academic_year_id])
    respond_with_object(groups)
  end
  
  
  # get "/lms/attendance/Group/fromCourseUnitVirtual", :controller => "Lms::Attendance::Group", :action => "from_course_unit_virtual"
  def from_course_unit_virtual
    groups = (Lms::Attendance::Group).from_course_unit_virtual_id(params[:course_unit_id], params[:academic_year_id])
    respond_with_object(groups)
  end
  
  # get "/lms/attendance/Group/:id/getAllStudents", :controller => "Lms::Attendance::Group", :action => "get_all_students"
  def get_all_students
    students = (Lms::Attendance::Group).get_all_students(params[:id])
    respond_with_object(students)
  end
  
end

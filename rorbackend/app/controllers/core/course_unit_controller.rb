class Core::CourseUnitController < ApplicationController
  caches_action :show, :cache_path => Proc.new { |c| c.params }
  caches_action :get_all_students, :cache_path => Proc.new { |c| c.params }
  # get "/core/CourseUnit/:id", :controller => "Core::CourseUnit", :action => "show"
  def show
    course_unit = (Core::CourseUnit).find(params[:id])
    respond_with_object(course_unit)
  end
  
  # get "core/CourseUnit/:id/getAllStudents", :controller => "Core::CourseUnit", :action => "get_all_students"
  def get_all_students
    students = (Core::CourseUnit).get_all_students(params[:id], params[:academic_year_id])
    respond_with_object(students)
  end
end

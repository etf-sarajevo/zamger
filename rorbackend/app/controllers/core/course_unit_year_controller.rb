class Core::CourseUnitYearController < ApplicationController
  # get "/core/CourseUnitYear/:id", :controller => "Core::CourseUnitYear", :action => "show"
  def show
    course_unit = (Core::CourseUnitYear).find(params[:id])
    respond_with_object(course_unit)
  end
  
  # get "/core/CourseUnitYear/fromCourseAndYear", :controller => "Core::CourseUnitYear", :action => "from_course_and_year"
  def from_course_and_year
    course_unit_year = (Core::CourseUnitYear).from_course_and_year(params[:course_unit_id], params[:academic_year_id])
    respond_with_object(course_unit_year)
  end
  
  # get "/core/CourseUnitYear/teacherAccessLevel", :controller => "Core::CourseUnitYear", :action => "teacher_access_level"
  def teacher_access_level
    access_level = (Core::CourseUnitYear).teacher_access_level(params[:teacher_id], [:course_unit_id], params[:academic_year_id])
    respond_with_object(access_level)
  end
end

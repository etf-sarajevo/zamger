class Core::PortfolioController < ApplicationController
  # get "/core/Portfolio/:id", :controller => "Core::Portfolio", :action => "show"
  def show
    portfolio = (Core::Portfolio).find(params[:id])
    respond_with_object(portfolio)
  end
  
  # get "/core/Portfolio/fromCourseOffering", :controller => "Core::Portfolio", :action => "from_course_offering"
  def from_course_offering
    portfolio = (Core::Portfolio).from_course_offering(params[:student_id], params[:course_offering_id])
    respond_with_object(portfolio)
  end
  
  #get "/core/Portfolio/:id/fromCourseUnit", :controller => "Core::Portfolio", :action => "from_course_unit"
  def from_course_unit
    portfolio = (Core::Portfolio).from_course_unit(params[:student_id], params[:course_unit_id], params[:academic_year_id])
    respond_with_object(portfolio)
  end
  
  # get "/core/Portfolio/:id/getGrade", :controller => "Core::Portfolio", :action => "get_grade"
  def get_grade
    grade = (Core::Portfolio).get_grade(params[:id])
    respond_with_object(grade)
  end
  
  # post "/core/Portfolio/:id/setGrade", :controller => "Core::Portfolio", :action => "set_grade"
  def set_grade
    respond_save((Core::Portfolio).set_grade(params[:id], params[:grade]))
  end
  
  # delete "/core/Portfolio/:id/deleteGrade", :controller => "Core::Portfolio", :action => "delete_grade"
  def delete_grade
    respond_delete((Core::Portfolio).delete_grade(params[:id]))
  end
  
  # get "/core/Portfolio/:id/getScore", :controller => "Core::Portfolio", :action => "get_score"
  def get_score
    score = (Core::Portfolio).get_score(params[:id], params[:scoring_element_id], params[:score])
    respond_with_object(score)
  end
  
  # post "/core/Portfolio/:id/setScore", :controller => "Core::Portfolio", :action => "set_score"
  def set_score
    respond_save((Core::Portfolio).set_score(params[:id], params[:scoring_element_id]))
  end
  
  # delete "/core/Portfolio/:id/deleteScore", :controller => "Core::Portfolio", :action => "delete_score"
  def delete_score
    respond_delete((Core::Portfolio).set_score(params[:id], params[:scoring_element_id]))
  end
  
  # get "/core/Portfolio/:id/getTotalScore", :controller => "Core::Portfolio", :action => "get_total_score"
  def get_total_score
    total_score = (Core::Portfolio).get_total_score(params[:id])
    respond_with_object(total_score)
  end
  
  # TODO
  # get "/core/Portfolio/:id/getMaxScore", :controller => "Core::Portfolio", :action => "get_max_score"
  def get_max_score
    max_score= (Core::Portfolio).get_max_score(params[:id])
    respond_with_object(max_score)
  end
  
  # get "/core/Portfolio/getLatestGradesForStudent", :controller => "Core::Portfolio", :action => "get_latest_grades_for_student"
  def get_latest_grades_for_student
    latest_grades = (Core::Portfolio).get_latest_grades_for_student(params[:student_id], params[:limit])
    respond_with_object(latest_grades)
  end
  
  # get "/core/Portfolio/getCurrentForStudent", :controller => "Core::Portfolio", :action => "get_current_for_student"
  def get_current_for_student
    current = (Core::Portfolio).get_current_for_student(params[:student_id])
    respond_with_object(current)        
  end
  

  # get "/core/Portfolio/getAllForStudent", :controller => "Core::Portfolio", :action => "get_all_for_student"
  def get_all_for_student
    all_portfolios = (Core::Portfolio).get_all_for_student(params[:student_id])
    respond_with_object(all_portfolios)
  end  

end

class Lms::Homework::ProgrammingLanguageController < ApplicationController
  # get "/lms/homework/programmingLanguage/:id", :controller => "Lms::Homework::ProgrammingLanguage", :action => "show"
  def show
    programming_language = (Lms::Homework::ProgrammingLanguage).find(params[:id])
    respond_with_object(programming_language)
  end

end

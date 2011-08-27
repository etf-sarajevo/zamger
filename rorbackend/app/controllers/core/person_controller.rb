class Core::PersonController < ApplicationController
  # get "/core/Person/:id", :controller => "Core::Person", :action => "show"
  def show
    person = (Core::Person).find(params[:id])
    respond_with_object(person)
  end
  
  # get "/core/Person/search", :controller => "Core::Person", :action => "search"
  def search
    result = (Core::Person).search(params[:query])
    
    respond_with_object(result)
  end

end

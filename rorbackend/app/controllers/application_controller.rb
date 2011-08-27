class ApplicationController < ActionController::Base
  protect_from_forgery
  before_filter :authenticate
  respond_to :json
  rescue_from ActiveRecord::RecordNotFound, :with => :record_not_found
  rescue_from ArgumentError, :with => :method_not_allowed
  def authenticate
    #@message = "Not allowed"
    #respond_to do |format|
    #  format.json { render :json => @message}
    #end
    #redirect_to "www.google.com"
  end
  
  def record_not_found
    render :nothing => true, :status => :not_found
  end
  
  def respond_save_ok
    render :nothing => true, :status => :ok
  end
  
  def respond_save_failed
    render :nothing => true, :status => :bad_request
  end
  
  def respond_save(saved)
    respond_save_ok() if saved
    respond_save_failed()
  end
  
  def respond_delete_ok
    render :nothing => true, :status => :ok
  end
  
  def respond_delete_failed
    render :nothing => true, :status => :bad_request
  end
  
  def respond_delete(deleted)
    respond_delete_ok() if deleted
    respond_delete_failed()
  end
  
  def respond_create_ok
    render :nothing => true, :status => :created
  end
  
  def respond_create_failed
    render :nothing => true, :status => :bad_request
  end
  
  def respond_create(created)
    respond_create_ok() if created
    respond_create_failed()
  end
  
  def respond_with_object(object)
    if (((object.kind_of? ActiveRecord::Relation) and (object.empty?)) or object == nil)
      raise ActiveRecord::RecordNotFound
    end
    respond_to do |format|
      format.json { render :json => object }
    end
  end
end

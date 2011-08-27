class Lms::Forum::ForumPostController < ApplicationController
  # get "/Lms/Forum/ForumPost/:id", :controller => "Lms::Forum::ForumPost", :action => "show"
  def show
    post = (Lms::Forum::ForumPost).from_id(params[:id])
    respond_with_object(post)
  end
  
  # delete "/Lms/Forum/ForumPost/:id", :controller => "Lms::Forum::ForumPost", :action => "delete"
  def delete
    respond_delete((Lms::Forum::ForumPost).delete_o(params[:id]))
  end
  
  # post "/Lms/Forum/ForumPost/:id", :controller => "Lms::Forum::ForumPost", :action => "update"
  def update
    respond_save((Lms::Forum::ForumPost).update_o(params[:id], params[:subject], params[:text]))
  end

end

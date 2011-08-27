class Lms::Forum::ForumTopicController < ApplicationController
  # get "/Lms/Forum/ForumTopic/:id", :controller => "Lms::Forum::Forumtopic", :action => "show"
  def show
    forum_topic = (Lms::Forum::ForumTopic).find(params[:id])
    respond_with_object(forum_topic)
  end
  # get "/Lms/Forum/ForumTopic/:id/getCountReplies", :controller => "Lms::Forum::Forumtopic", :action => "get_count_replies"
  def get_count_replies
    replies_count = (Lms::Forum::ForumPost).get_replies_count(params[:id])
    respond_with_object(replies_count)
  end
  
  # post "/Lms/Forum/ForumTopic/:id/viewed", :controller => "Lms::Forum::Forumtopic", :action => "viewed"
  def viewed
    respond_save((Lms::Forum::ForumTopic).viewed(params[:id]))
  end
  
  # get "/Lms/Forum/ForumTopic/:id/getAllPosts", :controller => "Lms::Forum::Forumtopic", :action => "get_all_posts"
  def get_all_posts
    topic_posts = (Lms::Forum::ForumTopic).get_all_posts(params[:id], params[:limit])
    respond_with_object(topic_posts)
  end
  
  # put "/Lms/Forum/ForumTopic/:id/addReply", :controller => "Lms::Forum::Forumtopic", :action => "add_reply"
  def add_reply
    respond_create((Lms::Forum::ForumTopic).add_reply(params[:id], params[:subject], params[:author_id], params[:text]))
  end

end

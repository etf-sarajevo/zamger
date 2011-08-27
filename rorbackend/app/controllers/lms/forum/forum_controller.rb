class Lms::Forum::ForumController < ApplicationController
  # get "/Lms/Forum/Forum/:id/getAllTopics", :controller => "Lms::Poll::Forum", :aciton => "get_all_topics"
  def get_all_topics
    all_topics = (Lms::Forum::ForumTopic).get_all_topics(params[:id], params[:limit])
    respond_with_object(all_topics)
  end
  
  # get "/Lms/Forum/Forum/:id/getTopicsCount", :controller => "Lms::Poll::Forum", :aciton => "get_topics_count"
  def get_topics_count
    topics_count = (Lms::Forum::ForumTopic).get_topics_count(params[:id])
    respond_with_object(topics_count)
  end
  
  # get "/Lms/Forum/Forum/:id/getLatestPosts", :controller => "Lms::Poll::Forum", :aciton => "get_latest_posts"
  def get_latest_posts
    latest_posts = (Lms::Forum::ForumTopic).get_latest_posts(params[:id], params[:limit])
    respond_with_object(latest_posts)
  end
  
  # put "/Lms/Forum/Forum/:id/startNewTopic", :controller => "Lms::Poll::Forum", :aciton => "start_new_topic"
  def start_new_topic
    respond_create((Lms::Forum::ForumTopic).start_new_topic(params[:id], params[:author_id], params[:post_id]))
  end

end

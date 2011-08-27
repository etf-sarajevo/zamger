class Lms::Forum::ForumPost < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'bb_post'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :subject, :naslov
  # alias_attribute :time, :vrijeme
  # alias_attribute :author_id, :osoba
  # alias_attribute :forum_topic_id, :tema

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'bb_post'
  # ID = TABLE_NAME + '.' + 'id'
  # SUBJECT =  TABLE_NAME + '.' + 'naslov'
  # TIME =  TABLE_NAME + '.' + 'vrijeme'
  # AUTHOR_ID =  TABLE_NAME + '.' + 'osoba'
  # FORUM_TOPIC_ID =  TABLE_NAME + '.' + 'tema'
 
  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_forum_forum_posts'
  ID = TABLE_NAME + '.' + 'id'
  SUBJECT =  TABLE_NAME + '.' + 'subject'
  TIME =  TABLE_NAME + '.' + 'time'
  AUTHOR_ID =  TABLE_NAME + '.' + 'author_id'
  FORUM_TOPIC_ID =  TABLE_NAME + '.' + 'forum_topic_id'

  ALL_COLUMNS = [ID, SUBJECT, TIME, AUTHOR_ID, FORUM_TOPIC_ID]
  
  
  belongs_to :author, :class_name => "Core::Person"
  belongs_to :topic
  has_one :forum_post_text
  
  def self.from_id(id)
    select_columns = [(Lms::Forum::ForumPost).SUBJECT, (Lms::Forum::ForumPost).TIME, (Lms::Forum::ForumPost).AUTHOR_ID, (Lms::Forum::ForumPost).TOPIC_ID, (Lms::Forum::ForumPost).TEXT, (Core::Person).ID, (Core::Person).NAME, (Core::Person).SURNAME, (Core::Person).STUDENT_ID_NUMBER, (Core::Auth).EMAIL]
    post = (Lms::Forum::ForumPost).joins(:author => :auth).joins(:forum_post_text).where(:id => id).select(select_columns)
    
    return post
  end
  
  
  def self.delete_o(id)
    (Lms::Forum::ForumPost).transaction do
      (Lms::Forum::ForumPostText).transaction do
        num_returned = 0
        num_returned += (Lms::Forum::ForumPost).delete(id)
        num_returned += (Lms::Forum::ForumPostText).delete_all(:forum_post_id => id)
        raise ActiveRecord::RecordNotFound if (num_returned == 0)
      end
    end
    return true
  end
  
  def update_o(id, subject, text)
    (Lms::Forum::ForumPost).transaction do
      (Lms::Forum::ForumPostText).transaction do
        forum_post = (Lms::Forum::ForumPost).find(id)
        forum_post_text = (Lms::Forum::ForumPostText).where(:forum_post_id => id).first
        forum_post.subject = subject
        forum_post_text = text
        
        return (forum_post.save and forum.post_text.save)
      end
    end
  end
end

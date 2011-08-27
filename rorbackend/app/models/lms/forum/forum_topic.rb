class Lms::Forum::ForumTopic < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'bb_tema'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :last_update, :vrijeme
  # alias_attribute :first_post_id, :prvi_post
  # alias_attribute :last_post_id, :zadnji_post
  # alias_attribute :views, :pregleda
  # alias_attribute :author_id, :osoba
  # alias_attribute :forum_id, :projekat

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'bb_tema'
  # ID = TABLE_NAME + '.' + 'id'
  # LAST_UPDATE = TABLE_NAME + '.' + 'vrijeme'
  # FIRST_POST_ID = TABLE_NAME + '.' + 'prvi_post'
  # LAST_POST_ID = TABLE_NAME + '.' + 'zadnji_post'
  # VIEWS = TABLE_NAME + '.' + 'pregleda'
  # AUTHOR_ID = TABLE_NAME + '.' + 'osoba'
  # FORUM_ID = TABLE_NAME + '.' + 'projekat'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_forum_forum_topics'
  ID = TABLE_NAME + '.' + 'id'
  LAST_UPDATE = TABLE_NAME + '.' + 'last_update'
  FIRST_POST_ID = TABLE_NAME + '.' + 'first_post_id'
  LAST_POST_ID = TABLE_NAME + '.' + 'last_post_id'
  VIEWS = TABLE_NAME + '.' + 'views'
  AUTHOR_ID = TABLE_NAME + '.' + 'author_id'
  FORUM_ID = TABLE_NAME + '.' + 'forum_id'

  ALL_COLUMNS = [ID, LAST_UPDATE, FIRST_POST_ID, LAST_POST_ID, VIEWS, AUTHOR_ID, FORUM_ID]
  
  belongs_to :first_post, :class_name => "Lms::Forum::ForumPost"
  belongs_to :last_post, :class_name => "Lms::Forum::ForumPost"
  belongs_to :author, :class_name => "Core::Person"
  belongs_to :forum
  has_many :forum_posts
  before_save :set_time_to_now
  
  def self.get_replies_count(id)
    replies_count = (Lms::Forum::ForumPost).where(:forum_topic_id => id).count
    replies_count -= 1
    
    return replies_count
  end
  
  def self.viewed(id)
    forum_topic = (Lms::Forum::ForumTopic).find(id)
    forum_topic[:views] += 1
    return forum_topic.save
  end
  
  
  
  def self.get_all_posts(id, limit)
    limit = nil if limit == '0'
    select_columns = (Lms::Forum::ForumPost).ALL_COLUMNS | [(Core::Forum::ForumPostText).TEXT, (Core::People).ID, (Core::People).NAME, (Core::People).SURNAME, (Core::People).STUDENT_ID_NUMBER]
    topic_posts = (Lms::Forum::ForumPost).where(:forum_topic_id => id).joins([:author, :forum_post_text]).select(select_columns).order((Lms::Forum::ForumPost).TIME + " ASC").limit(limit)
      
    return topic_posts
  end
  
  
  def self.add_reply(id, subject, author_id, text)
    (Lms::Forum::ForumTopic).transaction do
      (Lms::Forum::ForumPost).transaction do
        topic = (Lms::Forum::ForumTopic).find[id]
        raise ActiveRecord::RecordNotFound if topic == nil
        new_post = (Lms::Forum::ForumPost).new(:subject => subject, :author_id => author_id, :forum_topic_id => id)
        new_post.save!
        new_post_text = (Lms::Forum::ForumPostText).new(:text => text, :forum_post_id => new_post[:id])
        new_post_text.save!
        topic[:last_post_id] = new_post[:id]
        return true
      end
    end
  end
  
  
private
  
  def set_time_to_now
    self.time = Time.now
  end
end

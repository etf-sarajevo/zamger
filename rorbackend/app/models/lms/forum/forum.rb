class Lms::Forum::Forum < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'projekat'
  # set_primary_key :id
  # alias_attribute :id, :id
  # alias_attribute :name, :naziv
  # alias_attribute :course_unit_id, :predmet
  # alias_attribute :academic_year_id, :akademska_godina
  # alias_attribute :description, :opis
  # alias_attribute :note, :zabiljeska
  # alias_attribute :time, :vrijeme

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'projekat'
  # ID = TABLE_NAME + '.' + 'id'
  # NAME = TABLE_NAME + '.' + 'naziv'
  # COURSE_UNIT_ID = TABLE_NAME + '.' + 'predmet'
  # ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'akademska_godina'
  # DESCRIPTION = TABLE_NAME + '.' + 'opis'
  # NOTE = TABLE_NAME + '.' + 'zabiljeska'
  # TIME = TABLE_NAME + '.' + 'vrijeme'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_forum_forums'
  ID = TABLE_NAME + '.' + 'id'
  NAME = TABLE_NAME + '.' + 'name'
  COURSE_UNIT_ID = TABLE_NAME + '.' + 'course_unit_id'
  ACADEMIC_YEAR_ID = TABLE_NAME + '.' + 'academic_year_id'
  DESCRIPTION = TABLE_NAME + '.' + 'description'
  NOTE = TABLE_NAME + '.' + 'note'
  TIME = TABLE_NAME + '.' + 'time'

  ALL_COLUMNS = [ID, NAME, COURSE_UNIT_ID, ACADEMIC_YEAR_ID, DESCRIPTION, NOTE, TIME]
  
  
  belongs_to :course_unit, :class_name => "Core::CourseUnit"
  belongs_to :academic_year, :class_name => "Core::AcademicYear"
  has_many :forum_topics
  
  
  def self.get_all_topics(id, limit)
    all_topics = (Lms::Forum::ForumTopic).includes(:last_post).where(:forum_id => id).order((Lms::Forum::ForumPost).TIME + " DESC").limit(limit)
    
    return all_topics
  end
  
  
  def self.get_topics_count(id)
    topics_count = (Lms::Forum::ForumTopic).where(:forum_id => id).count
    
    return topics_count
  end
  
  def self.get_latest_posts(id, limit)
    limit = nil if limit == '0'
    select_columns = (Lms::Forum::ForumPost).ALL_COLUMNS | [(Core::Forum::ForumPostText).TEXT, (Core::People).ID, (Core::People).NAME, (Core::People).SURNAME, (Core::People).STUDENT_ID_NUMBER]
    latest_posts = (Lms::Forum::ForumTopic).includes(:forum_posts => :forum_post_text).where(:forum_id => id).select(select_columns).limit(limit).order((Lms::Forum::ForumPost).TIME + " DESC")
    
    return latest_posts
  end
  
  
  def self.start_new_topic(id, author_id, post_id)
    new_topic = (Lms::Forum::ForumTopic).new(:first_post_id => post_id, :last_post_id => post_id, :views => 0, :author_id => author_id, :forum_id => id)
    
    return new_topic.save
  end
  
  
end

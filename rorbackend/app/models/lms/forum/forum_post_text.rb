class Lms::Forum::ForumPostText < ActiveRecord::Base
  # Uncomment following lines if working with legacy database
  # set_table_name 'bb_post_text'
  # set_primary_key :post
  # alias_attribute :forum_post_id, :post
  # alias_attribute :text, :tekst

  # Uncomment following lines if working with legacy database
  # TABLE_NAME = 'bb_post_text'
  # FORUM_POST_ID = TABLE_NAME + '.' + 'post'
  # TEXT = TABLE_NAME + '.' + 'tekst'

  # Comment following lines if working with legacy database
  TABLE_NAME = 'lms_forum_forum_post_texts'
  FORUM_POST_ID = TABLE_NAME + '.' + 'forum_post_id'
  TEXT = TABLE_NAME + '.' + 'text'

  ALL_COLUMNS = [FORUM_POST_ID, TEXT]
  
  belongs_to :forum_post
end

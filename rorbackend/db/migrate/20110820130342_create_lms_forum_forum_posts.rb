class CreateLmsForumForumPosts < ActiveRecord::Migration
  def change
    create_table :lms_forum_forum_posts do |t|
      t.string :subject, :limit => 300
      t.time :time
      t.integer :author_id
      t.integer :forum_topic_id

      # t.timestamps
    end
  end
end

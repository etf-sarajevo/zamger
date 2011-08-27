class CreateLmsForumForumTopics < ActiveRecord::Migration
  def change
    create_table :lms_forum_forum_topics do |t|
      t.time :last_update
      t.integer :first_post_id
      t.integer :last_post_id
      t.integer :views
      t.integer :author_id
      t.integer :forum_id

      # t.timestamps
    end
  end
end

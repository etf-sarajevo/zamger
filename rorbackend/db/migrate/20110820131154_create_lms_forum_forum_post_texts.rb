class CreateLmsForumForumPostTexts < ActiveRecord::Migration
  def change
    create_table :lms_forum_forum_post_texts do |t|
      t.integer :forum_post_id
      t.text :text

      # t.timestamps
    end
  end
end

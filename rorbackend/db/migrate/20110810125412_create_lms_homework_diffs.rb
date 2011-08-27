class CreateLmsHomeworkDiffs < ActiveRecord::Migration
  def change
    create_table :lms_homework_diffs do |t|
      t.integer :assignment_id, :default => 0
      t.text :diff
      
      # t.timestamps
    end
  end
end

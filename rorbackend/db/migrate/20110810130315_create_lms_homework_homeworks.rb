class CreateLmsHomeworkHomeworks < ActiveRecord::Migration
  def change
    create_table :lms_homework_homeworks do |t|
      t.string :name, :limit => 50
      t.integer :course_unit_id, :default => 0
      t.integer :academic_year_id, :default => 0
      t.integer :nr_assignments, :default => 0
      t.float :score, :default => 0
      t.time :deadline, :default => nil
      t.boolean :active, :default => false
      t.integer :programming_language_id, :default => 0
      t.boolean :attachment, :default => false
      t.string :allowed_extensions, :default => nil
      t.string :text, :default => nil
      t.integer :scoring_element_id
      t.time :published_date_time

      # t.timestamps
    end
  end
end

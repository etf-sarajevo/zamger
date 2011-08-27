class CreateLmsMoodleMoodleIds < ActiveRecord::Migration
  def change
    create_table :lms_moodle_moodle_ids do |t|
      t.integer :course_unit_id
      t.integer :academic_year_id
      t.integer :moodle_id
      # TODO Dodati tabelu na koju se referencira moodle_id u model
      # t.timestamps
    end
  end
end

class CreateLmsPollPollResults < ActiveRecord::Migration
  def change
    create_table :lms_poll_poll_results do |t|
      t.integer :poll_id
      t.time :time
      t.enum :closed, :limit => ['Y', 'N']
      t.integer :course_unit_id
      t.string :unique_id, :limit => 50
      t.integer :academic_year_id
      t.integer :programme_id
      t.integer :semester

      # t.timestamps
    end
  end
end

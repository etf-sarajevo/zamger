class CreateLmsPollPollResults < ActiveRecord::Migration
  def change
    create_table :lms_poll_poll_results do |t|
      t.integer :poll_id
      t.time :time
      # mysql version
      # t.enum :closed, :limit => ['Y', 'N']
      # postgresql version
      t.string :closed, :limit => 1
      t.integer :course_unit_id
      t.string :unique_id, :limit => 50
      t.integer :academic_year_id
      t.integer :programme_id
      t.integer :semester

      # t.timestamps
    end
  end
end

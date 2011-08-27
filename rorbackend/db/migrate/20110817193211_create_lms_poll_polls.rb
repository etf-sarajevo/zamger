class CreateLmsPollPolls < ActiveRecord::Migration
  def change
    create_table :lms_poll_polls do |t|
      t.time :open_date
      t.time :close_date
      t.string :name, :limit => 255
      t.text :description
      t.boolean :active
      t.boolean :editable
      t.integer :academic_year_id

      # t.timestamps
    end
  end
end

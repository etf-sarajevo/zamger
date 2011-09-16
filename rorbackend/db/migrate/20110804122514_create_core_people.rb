class CreateCorePeople < ActiveRecord::Migration
  def change
    create_table :core_people do |t|
      t.string :name, :limit => 30
      t.string :surname, :limit => 30
      t.string :fathers_name, :limit => 30
      t.string :fathers_surname, :limit => 30
      t.string :mothers_name, :limit => 30
      t.string :mothers_surname, :limit => 30
      # mysql version
      # t.enum :gender, :limit => ['M', 'Z', '']
      # postgresql version
      t.string :gender, :limit => 1
      t.string :email, :limit => 100
      t.string :student_id_number, :limit => 10
      t.date :date_of_birth
      t.integer :place_of_birth_id
      t.integer :ethnicity_id
      t.integer :nationality_id
      t.boolean :soldier_category
      t.string :personal_id_number, :limit => 14
      t.string :address, :limit => 50
      t.integer :address_place_id
      t.string :phone, :limit => 15
      t.integer :canton_id
      t.boolean :for_delete, :default => false
      t.integer :professional_level_id
      t.integer :science_level_id
      t.string :picture, :limit => 50

      # t.timestamps
    end
  end
end

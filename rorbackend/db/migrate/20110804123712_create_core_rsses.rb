class CreateCoreRsses < ActiveRecord::Migration
  def change
    create_table :core_rsses do |t|
      t.integer :auth_id
      t.time :accessed_at

      # t.timestamps
    end
  end
end
